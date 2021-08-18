<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\CnpjFundo;
use DateTime;
use DatePeriod;
use DateInterval;

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

		// Calcula patrimonio total de uma carteira e dos fundos individuais
		$patrimonio = 0;
		$data[] = "['Fundo Id', 'Patrimonio Total do Fundo'],";
		$id_fundo_unique = array();
		$patrimonios[][] = "['Fundo id']['Data fundo'],";

		
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$patrimonio += $operacoes->valor_total;
			$data[$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];
			$id_fundo_unique[] = $operacoes->cnpj_fundo_id;
		endforeach;
		$id_fundo_unique = array_unique($id_fundo_unique);
		
		// fim

		// pega todas datas desde a primeira operacao até o dia atual
		$first_data = new DateTime('01/01/2100');
		$first_data->format('Y-m-d');
		$data_list = array();
		$aux_data = $first_data->getTimestamp();
		$datasDaCarteira = array();

		// 10 5 7 8 9  999999999
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$lowest_data = $operacoes->data->getTimestamp();
			$datasDaCarteira[] = $operacoes->data;
			if ($lowest_data < $aux_data) :
				$aux_data = $lowest_data;
				$var_aux = $operacoes->data;
			endif;		
		endforeach;

		sort($datasDaCarteira); // datas da carteira ordenadas asc
		$datasDaCarteira = array_unique($datasDaCarteira);

		$primeira_data = date('Y/m/d', $var_aux->getTimestamp());
		$segunda_data = date('Y/m/d');
		$data_auxiliar = $primeira_data;

		while (strtotime($primeira_data) < strtotime($segunda_data)) :
			$data_list[] = date('d/m/Y', strtotime($primeira_data));
			$primeira_data = date('Y/m/d', strtotime("$primeira_data +1 day"));			
		endwhile;


		$patrimonioTest = [];
		//$patrimonio_Total = 0;
		$data_anterior = 0;
		
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$auxiliar_data = (string) $operacoes->data;
			$patrimonioTest[$auxiliar_data][$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];

			$data_anterior = $auxiliar_data;
		endforeach;
		
		

		$this->set(compact('patrimonio', 'data', 'id_fundo_unique', 'patrimonios', 'first_data', 'data_list', 'var_aux', 'data_auxiliar',
		 'patrimonioTest','datasDaCarteira', 'patrimonio_Total'));
		
		

		
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
