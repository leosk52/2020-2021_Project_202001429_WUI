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

		// Calcula patrimonio total de uma carteira e dos fundos individuais
		$patrimonio_por_fundo = array(); //patrimonio[fundo] = valorTotalPorFundo
		$id_fundo_unique = array();	
		$patrimonio_total = [];
		$data_anterior = 0;
		
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$auxiliar_data = (string) $operacoes->data;

			$patrimonio_total[$auxiliar_data][$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];
			
			$patrimonio_por_fundo[$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];

			$id_fundo_unique[] = $operacoes->cnpj_fundo_id;

			$data_anterior = $auxiliar_data;
		endforeach;

		$id_fundo_unique = array_unique($id_fundo_unique);		
		
		$exibe = array();
		$exibe[] = "['Data', 'Patrimônio Líquido Total'],";

		/*
		foreach ($id_fundo_unique as $fundoId) {
			$exibe[0] += $fundoId. ",";
		}
		$exibe[0] += "]";
		*/
		$valorReal = [];
		$dataQueVouUsar = $this->calcula_datas_carteira($carteirasInvestimento);
		// JUST TO UPDATE GIT CORRECTLY
		foreach ($dataQueVouUsar as $data) {
			foreach ($patrimonio_total[(string)$data] as $fundoId => $patrimonio) {
				//$valorReal[$fundoId] += $patrimonio;
				$valorReal["total"] += $patrimonio;
				//$aux = $fundoId;
			}
			//$exibe[] = "['" . (string)$data . "'," . $valorReal["total"] . $valorReal[$aux] . ",";
			$exibe[] = "['" . (string)$data . "'," . $valorReal["total"] . "],";
			//foreach ($id_fundo_unique as $fundoId) {
				// $exibe[count($exibe)-1] += $valorReal[$fundoId] . ",";
			//}
		}	
		
		$this->set(compact('exibe'));
		
		

		
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
