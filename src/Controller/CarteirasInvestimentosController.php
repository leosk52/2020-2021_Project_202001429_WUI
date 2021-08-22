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

	public function calcula_datas_carteira($carteirasInvestimento) {
		// pega todas datas desde a primeira operacao até o dia atual
		$first_data = new DateTime('01/01/2100');
		$first_data->format('Y-m-d');
		$data_list = [];
		$datasDaCarteira = [];
		
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$datasDaCarteira[] = $operacoes->data;
		endforeach;

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

	public function calcula_primeira_data($carteirasInvestimento) {
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$datasDaCarteira[] = $operacoes->data;
		endforeach;
		sort($datasDaCarteira);

		return $datasDaCarteira[0];
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

		//var_dump($carteirasInvestimento->id);
		//$this->set(compact('carteirasInvestimento'));
		
		$operacoes = $this->CarteirasInvestimentos->OperacoesFinanceiras->find('all');
		//$this->set(compact('operacoes'));

		if (sizeof($carteirasInvestimento->operacoes_financeiras) == 0) return;

		// Preciso inicializar todas variaveis antes do for de cada op
		$datas_totais = $this->calcula_datas_carteira($carteirasInvestimento);
		//var_dump($datas_totais[0]);
		$rentabilidade_fundo = [];
		
		$busca_tipo_operacao = $this->CarteirasInvestimentos->OperacoesFinanceiras->TipoOperacoesFinanceiras->find('all');
		//var_dump($busca_tipo_operacao);
		foreach ($busca_tipo_operacao as $tipo_operacao) {
			$tipo_de_operacao[$tipo_operacao['id']] = $tipo_operacao['is_aplicacao'];
		}

		// Calcula patrimonio total de uma carteira e dos fundos individuais
		$id_fundo_unique = [];	
		$balanco_fundo = [];
				
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :

			$id_fundo_unique[] = $operacoes->cnpj_fundo_id;

			$auxiliar_data = (string) $operacoes->data;
			$auxiliar_fundo = $operacoes->cnpj_fundo_id;
			$valor_total = $operacoes["valor_total"];

			// se a operacao eh de deposito acrescenta, outros tipos diminui
			if ($tipo_de_operacao[$operacoes['tipo_operacoes_financeira_id']] == 1) {
				$balanco_fundo[$auxiliar_data][$auxiliar_fundo] += $valor_total;
			} else {
				$balanco_fundo[$auxiliar_data][$auxiliar_fundo] -= $valor_total;
			}

		endforeach;

		$id_fundo_unique = array_unique($id_fundo_unique);

		$primeira_data_carteira = $this->calcula_primeira_data($carteirasInvestimento);
		$primeira_data_format = date('Y/m/d', $primeira_data_carteira->getTimestamp());

		// busca rentabilidade
		foreach ($id_fundo_unique as $fundo_id) {				
			$busca_rentabilidade = $this->CarteirasInvestimentos->OperacoesFinanceiras->CnpjFundos->DocInfDiarioFundos->find('all',
		 		['order' => ['DT_COMPTC' => 'ASC']])->where(['cnpj_fundo_id' => $fundo_id, 'DT_COMPTC >=' => $primeira_data_format]);

			foreach ($busca_rentabilidade as $busca) {
				$data = date('d/m/Y', $busca['DT_COMPTC']->getTimestamp());
				$rentabilidade_fundo[$data][$fundo_id] = $busca['rentab_diaria'];
			}
		}
		
		// fors pra exibir os valores no grafico patrimonio
		$patrimonio_total_view = [];
		$patrimonio_fundo_view = [];
		$drawdown = [];
		$data_anterior = '';

		foreach ($datas_totais as $data) {
			$soma_fundos = 0;
			$soma_drawdown_fundo = 0;
			foreach ($id_fundo_unique as $fundo_id) {

				$rendimento_dia = $balanco_fundo[$data_anterior][$fundo_id] * $rentabilidade_fundo[$data][$fundo_id]; // rend =500 * 0.053942641160
				//var_dump($rendimento_dia);
				$balanco_fundo[$data][$fundo_id] += $balanco_fundo[$data_anterior][$fundo_id] + $rendimento_dia; //$patrimonio_dia_anterior; // 5000, 5000 + (5000*0.00005)
				//var_dump($balanco_fundo[$data][$fundo_id]);

				if ($balanco_fundo[$data][$fundo_id] < $balanco_fundo[$data_anterior][$fundo_id]) {
					$drawdown[$data][$fundo_id] = ($balanco_fundo[$data_anterior][$fundo_id] - $balanco_fundo[$data][$fundo_id]) / $balanco_fundo[$data_anterior][$fundo_id];
				} else {
					$drawdown[$data][$fundo_id] = 0;
				}

				$patrimonio_fundo_view[$data][$fundo_id] = $balanco_fundo[$data][$fundo_id];
				//var_dump($patrimonio_fundo_view);
				$soma_fundos += $balanco_fundo[$data][$fundo_id];
			}
			$patrimonio_total_view[$data]["total"] = $soma_fundos;
			$data_anterior = $data;
		}
		
		$this->set(compact('id_fundo_unique', 'datas_totais', 'patrimonio_total_view', 'patrimonio_fundo_view', 'drawdown',
		 'IndicadoresCarteiras', 'carteirasInvestimento'));
		
	
		/*
		$this->paginate = [
			'contain' => ['CarteirasInvestimentos', 'CnpjFundos', 'DistribuidorFundos', 'TipoOperacoesFinanceiras'],
		];
		$operacoesFinanceiras = $this->paginate($this->OperacoesFinanceiras);

		$this->set(compact('operacoesFinanceiras'));
		 *
		 */
		//$this->set((compact('IndicadoresCarteiras')));
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
