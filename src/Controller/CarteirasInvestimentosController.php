<?php

declare(strict_types=1);

namespace App\Controller;

use DateTime;
use Cake\ORM\TableRegistry;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

/**
 * CarteirasInvestimentos Controller
 *
 * @property \App\Model\Table\CarteirasInvestimentosTable $CarteirasInvestimentos
 * @method \App\Model\Entity\CarteirasInvestimento[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CarteirasInvestimentosController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void Renders view
	 */
	public function index() {
		$session = $this->request->getSession();
		$userId = $session->read('User.id');
		$userName = $session->read('User.nome');
		$carteirasInvestimentos = $this->paginate($this->CarteirasInvestimentos->find()->where(['usuario_id' => $userId]));
		//var_dump($session);
		//exit();
		$this->set(compact('carteirasInvestimentos', 'userName'));
	}

	public function calcula_datas_carteira($operacoes) {
		$data_list = [];
		$datasDaCarteira = [];
		
		foreach ($operacoes as $operacao) {
			$datasDaCarteira[] = $operacao->data;
		}
		sort($datasDaCarteira); // datas da carteira ordenadas asc
		$datasDaCarteira = array_unique($datasDaCarteira);
				
		$primeira_data = date('Y/m/d', $datasDaCarteira[0]->getTimestamp());
		
		$segunda_data = date('Y/m/d');
		
		while (strtotime($primeira_data) < strtotime($segunda_data)) :
			$data_list[] = date('d/m/Y', strtotime($primeira_data));
			$primeira_data = date('Y/m/d', strtotime("$primeira_data +1 day"));
		endwhile;

		return $data_list;
	}

	public function calcula_patrimonio($datas_totais, $id_fundo_unique, $balanco_fundo) {
		$ret_dados = [];
		
		foreach ($datas_totais as $data) {
			$soma_fundos = 0;
			foreach ($id_fundo_unique as $fundo_id) {
				$balanco_atual = $balanco_fundo[$data][$fundo_id];

				$ret_dados[$data][$fundo_id] = $balanco_atual;

				$soma_fundos += $balanco_atual;
			}
			$ret_dados[$data]["total"] = $soma_fundos;
		}

		return $ret_dados;
	}

	public function calcula_drawdown($datas_totais, $calculo_patrimonio) {
		$ret_dados = [];
		$valor_maximo_carteira = 0;

		foreach ($datas_totais as $data) {		
			$calculo_patrimonio_aux = $calculo_patrimonio[$data]["total"];
			
			if ($calculo_patrimonio_aux > $valor_maximo_carteira) {
				$valor_maximo_carteira = $calculo_patrimonio_aux;
			}
			
			if ($calculo_patrimonio_aux < $valor_maximo_carteira) {
				$ret_dados[$data]["total"] = ($valor_maximo_carteira - $calculo_patrimonio_aux) / $valor_maximo_carteira;
			} else {
				$ret_dados[$data]["total"] = 0;
			}
		}

		return $ret_dados;
	}

	public function calcula_rentab($datas_totais, $id_fundo_unique, $balanco_fundo, $rendimento_dia, $calculo_patrimonio) {
		$ret_dados = [];
		$soma_rentabilidade = 0;

		foreach ($datas_totais as $data) {
			foreach ($id_fundo_unique as $fundo_id) {
				$balanco_atual = $balanco_fundo[$data][$fundo_id];
				$rendimento_atual = $rendimento_dia[$data][$fundo_id];
				
				if ($balanco_atual != 0) {
					$ret_dados[$data][$fundo_id] = $rendimento_atual / $balanco_atual;
				} else {
					$ret_dados[$data][$fundo_id] = 0;
				}
				$soma_rentabilidade += $rendimento_atual;
			}
			if ($calculo_patrimonio[$data]["total"] != 0) {
				$ret_dados[$data]["total"] = $soma_rentabilidade / $calculo_patrimonio[$data]["total"];
			} else {
				$ret_dados[$data]["total"] = 0;
			}
		}

		return $ret_dados;
	}


	public function indicadores($id = null) {
		
		$carteirasInvestimento = $this->CarteirasInvestimentos->get($id, [
			'contain' => ['Usuarios', 'IndicadoresCarteiras', 'OperacoesFinanceiras'=>['CnpjFundos', 'DistribuidorFundos', 'TipoOperacoesFinanceiras']],
		]);
		
		$this->set(compact('carteirasInvestimento'));
		

		$operacoes = $this->CarteirasInvestimentos->OperacoesFinanceiras->find('all', ['order' => ['data' => 'ASC']])->where(['carteiras_investimento_id' => $id])->toList();
		
		if (sizeof($operacoes) == 0) return;
		$datas_totais = $this->calcula_datas_carteira($operacoes);

		$rentabilidade_fundo = [];		
	
		$busca_tipo_operacao = $this->CarteirasInvestimentos->OperacoesFinanceiras->TipoOperacoesFinanceiras->find('all');
		foreach ($busca_tipo_operacao as $tipo_operacao) {
			$tipo_de_operacao[$tipo_operacao['id']] = $tipo_operacao['is_aplicacao'];
		}
		
		// Calcula patrimonio total de uma carteira e dos fundos individuais
		$id_fundo_unique = [];	
		$balanco_fundo = [];

		$primeira_data_format = ($operacoes[0]['data']);
		$primeira_data_carteira = date('Y-m-d', $primeira_data_format->getTimestamp());

		foreach ($carteirasInvestimento->operacoes_financeiras as $operacao) {
			$fundo_id = $operacao['cnpj_fundo_id'];

			foreach ($datas_totais as $data) {
				$balanco_fundo[$data][$fundo_id] = 0;
				$rentabilidade_fundo[$data][$fundo_id] = 0;
			}

			$busca_rentabilidade = $this->CarteirasInvestimentos->OperacoesFinanceiras->CnpjFundos->DocInfDiarioFundos->find('all',
		 		['order' => ['DT_COMPTC' => 'ASC']])->where(['cnpj_fundo_id' => $fundo_id, 'DT_COMPTC >=' => $primeira_data_carteira]);				

			foreach ($busca_rentabilidade as $busca) {
				$data = date('d/m/Y', $busca['DT_COMPTC']->getTimestamp());
				$rentabilidade_fundo[$data][$fundo_id] = $busca['rentab_diaria'];
			}
		}
				
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacao) :

			$id_fundo_unique[] = $operacao->cnpj_fundo_id;

			$auxiliar_data = (string) $operacao->data;
			$auxiliar_fundo = $operacao->cnpj_fundo_id;
			$valor_total = $operacao["valor_total"];

			// se a operacao eh de deposito acrescenta, outros tipos diminui
			if ($tipo_de_operacao[$operacao['tipo_operacoes_financeira_id']] == 1) {
				$balanco_fundo[$auxiliar_data][$auxiliar_fundo] += $valor_total;
			} else {
				$balanco_fundo[$auxiliar_data][$auxiliar_fundo] -= $valor_total;
			}

		endforeach;

		$id_fundo_unique = array_unique($id_fundo_unique);

		$rendimento_dia[] = 0;
		$data_anterior = '';

		// calcula rendimento e balanco do dia e fundo
		foreach ($datas_totais as $data) {
			foreach ($id_fundo_unique as $fundo_id) {
				$rendimento_dia[$data][$fundo_id] = $balanco_fundo[$data_anterior][$fundo_id] * $rentabilidade_fundo[$data][$fundo_id];
				$balanco_fundo[$data][$fundo_id] += $balanco_fundo[$data_anterior][$fundo_id] + $rendimento_dia[$data][$fundo_id];		
				
			}
			$data_anterior = $data;
		}

		$calculo_patrimonio = $this->calcula_patrimonio($datas_totais, $id_fundo_unique, $balanco_fundo);
		$calculo_drawdown = $this->calcula_drawdown($datas_totais, $calculo_patrimonio);
		$calculo_rentab_percent = $this->calcula_rentab($datas_totais, $id_fundo_unique, $balanco_fundo, $rendimento_dia, $calculo_patrimonio);

		
		$this->set(compact('id_fundo_unique', 'calculo_patrimonio', 'datas_totais', 'calculo_drawdown', 'calculo_rentab_percent', 'IndicadoresCarteiras', 'carteirasInvestimento'));
		
	}
	
	/**
	 * View method
	 *
	 * @param string|null $id Carteiras Investimento id.
	 * @return \Cake\Http\Response|null|void Renders view
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$carteirasInvestimento = $this->CarteirasInvestimentos->get($id, [
			'contain' => ['Usuarios', 'IndicadoresCarteiras', 'OperacoesFinanceiras'=>['CnpjFundos', 'DistribuidorFundos', 'TipoOperacoesFinanceiras']],
		]);
		
		$this->indicadores($id);

		$this->set(compact('carteirasInvestimento'));
	
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$carteirasInvestimento = $this->CarteirasInvestimentos->newEmptyEntity();
		if ($this->request->is('post')) {
			$carteirasInvestimento = $this->CarteirasInvestimentos->patchEntity($carteirasInvestimento, $this->request->getData());
			if ($this->CarteirasInvestimentos->save($carteirasInvestimento)) {
				$this->Flash->success(__('The carteiras investimento has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The carteiras investimento could not be saved. Please, try again.'));
		}
		$usuarios = $this->CarteirasInvestimentos->Usuarios->find('list', ['limit' => 200]);
		$this->set(compact('carteirasInvestimento', 'usuarios'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Carteiras Investimento id.
	 * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function edit($id = null) {
		$carteirasInvestimento = $this->CarteirasInvestimentos->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$carteirasInvestimento = $this->CarteirasInvestimentos->patchEntity($carteirasInvestimento, $this->request->getData());
			if ($this->CarteirasInvestimentos->save($carteirasInvestimento)) {
				$this->Flash->success(__('The carteiras investimento has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The carteiras investimento could not be saved. Please, try again.'));
		}
		$usuarios = $this->CarteirasInvestimentos->Usuarios->find('list', ['limit' => 200]);
		$this->set(compact('carteirasInvestimento', 'usuarios'));
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Carteiras Investimento id.
	 * @return \Cake\Http\Response|null|void Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$carteirasInvestimento = $this->CarteirasInvestimentos->get($id);
		if ($this->CarteirasInvestimentos->delete($carteirasInvestimento)) {
			$this->Flash->success(__('The carteiras investimento has been deleted.'));
		} else {
			$this->Flash->error(__('The carteiras investimento could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/*
	 * *******************************************************************************
	 */

	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		$session = $this->request->getSession();
		$conectado = $session->read('User.id') != null;
		if (!$conectado) {
			$this->Flash->error(__('Você precisa estar logado para acessar a página solicitada. Você foi redirecionado à página principal.'));
			return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
		}
	}

}
