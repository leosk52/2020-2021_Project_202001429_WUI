<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\CnpjFundo;
use DateTime;
use DatePeriod;
use DateInterval;
use Cake\ORM\TableRegistry;

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
		$data_list = array();
		$datasDaCarteira = array();

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

		$this->set(compact('carteirasInvestimento'));
		
		$operacoes = $this->CarteirasInvestimentos->OperacoesFinanceiras->find('all');
		$this->set(compact('operacoes'));

		// nao ha operacoes na carteira
		if (sizeof($carteirasInvestimento->operacoes_financeiras) == 0) {
			return;
		}
		
		$busca_tipo_operacao = $this->CarteirasInvestimentos->OperacoesFinanceiras->TipoOperacoesFinanceiras->find('all');
		// saber qual tipo de operacao para somar/subtrair no patrimonio total // mudar o retorno, atual = bool
		//$busca_tipo_operacao = TableRegistry::getTableLocator()->get('TipoOperacoesFinanceiras')->find('all');
		//var_dump($busca_tipo_operacao);
		foreach ($busca_tipo_operacao as $tipo_operacao) {
			$tipo_de_operacao[$tipo_operacao['id']] = $tipo_operacao['is_aplicacao'];
		}

		//var_dump($tipo_de_operacao);

		// Calcula patrimonio total de uma carteira e dos fundos individuais
		$patrimonio_por_fundo = array(); //patrimonio[fundo] = valorTotalPorFundo
		$id_fundo_unique = array();	
		$patrimonio_total = [];
		$data_anterior = 0;
		
		$dataQueVouUsar = $this->calcula_datas_carteira($carteirasInvestimento);
		
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :

			//$id_fundo_unique[] = $operacoes->cnpj_fundo_id;

			$auxiliar_data = (string) $operacoes->data;

			// se a operacao eh de deposito acrescenta, outros tipos diminui
			if ($tipo_de_operacao[$operacoes['tipo_operacoes_financeira_id']] == 1) {
				$patrimonio_total[$auxiliar_data][$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];
			} else {
				$patrimonio_total[$auxiliar_data][$operacoes->cnpj_fundo_id] -= $operacoes["valor_total"];
			}
			
			//$patrimonio_por_fundo[$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];

			$data_anterior = $auxiliar_data;

		endforeach;

		//$id_fundo_unique = array_unique($id_fundo_unique);		
		
		$exibe = array();
		$exibe[] = "['Data', 'Patrimônio Líquido Total'],";
		//$exibe2 = array();
		//$exibe2[] = "['Data', 'Patrimônio Líquido Total'],";

		/*
		foreach ($id_fundo_unique as $fundoId) {
			$exibe[0] += $fundoId. ",";
		}
		$exibe[0] += "]";
		*/
		$patrimonio_view = [];

		foreach ($dataQueVouUsar as $data) {
			foreach ($patrimonio_total[(string)$data] as $fundoId => $patrimonio) {
				//var_dump($patrimonio);
				//$patrimonio_view[$fundoId] += $patrimonio;
				$patrimonio_view["total"] += $patrimonio;
				//$aux = $fundoId;
			}
			//$exibe[] = "['" . (string)$data . "'," . $patrimonio_view["total"] . $patrimonio_view[$aux] . ",";
			$exibe[] = "['" . (string)$data . "'," . $patrimonio_view["total"] . "],";
			//foreach ($id_fundo_unique as $fundoId) {
				// $exibe[count($exibe)-1] += $patrimonio_view[$fundoId] . ",";
			//}
		}

		/*
		$valorDif = [];
		foreach ($dataQueVouUsar as $data) {
			foreach ($rentab_data_fundo[$data] as $fundoId => $rentab) {
				var_dump($rentab);
				//$patrimonio_view[$fundoId] += $patrimonio;
				$valorDif["total"] += $rentab;
				//$aux = $fundoId;
			}
			//$exibe[] = "['" . (string)$data . "'," . $patrimonio_view["total"] . $patrimonio_view[$aux] . ",";
			$exibe2[] = "['" . (string)$data . "'," . $valorDif["total"] . "],";
			//var_dump($exibe2);
			//foreach ($id_fundo_unique as $fundoId) {
				// $exibe[count($exibe)-1] += $patrimonio_view[$fundoId] . ",";
			//}
		}
		*/
		
		$this->set(compact('exibe', 'exibe2'));
		
		

		
		/*
		$this->paginate = [
			'contain' => ['CarteirasInvestimentos', 'CnpjFundos', 'DistribuidorFundos', 'TipoOperacoesFinanceiras'],
		];
		$operacoesFinanceiras = $this->paginate($this->OperacoesFinanceiras);

		$this->set(compact('operacoesFinanceiras'));
		 *
		 */
		$this->set((compact('IndicadoresCarteiras')));
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
