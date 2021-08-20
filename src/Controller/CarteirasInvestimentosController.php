<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\CnpjFundo;
use DateTime;
use DatePeriod;
use DateInterval;
use Cake\ORM\TableRegistry;
use Symfony\Component\VarDumper\VarDumper;

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

		
		//$data1 = new DateTime('2011-09-11');
		//$data2 = new DateTime('2011-10-13');
		//$intervalo = $data1->diff($data2);
		//var_dump($intervalo->days);
		//echo $intervalo->format('%R%a dias');

		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :
			$datasDaCarteira[] = $operacoes->data;
		endforeach;

		sort($datasDaCarteira); // datas da carteira ordenadas asc
		$datasDaCarteira = array_unique($datasDaCarteira);
		
		$primeira_data = date('Y/m/d', $datasDaCarteira[0]->getTimestamp());
		//$dataaux = $datasDaCarteira[0];
		//var_dump($dataaux);
		$segunda_data = '2021/06/30';
		//$segunda_dataa = new DateTime('2021/06/30'); //date('Y/m/d', '30/06/2021');
		//$dif = $dataaux->diff($segunda_dataa);
		//var_dump($dif->days);


		while (strtotime($primeira_data) < strtotime($segunda_data)) :
		//$i = 0;
		//while ($i < $dif->days) :
			$data_list[] = date('d/m/Y', strtotime($primeira_data));
			$primeira_data = date('Y/m/d', strtotime("$primeira_data +1 day"));		
		//	$i++;	
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

		$this->set(compact('carteirasInvestimento'));
		
		$operacoes = $this->CarteirasInvestimentos->OperacoesFinanceiras->find('all');
		$this->set(compact('operacoes'));

		// nao ha operacoes na carteira
		if (sizeof($carteirasInvestimento->operacoes_financeiras) == 0) {
			return;
		}

		// Preciso inicializar todas variaveis antes do for de cada op
		$datas_totais = $this->calcula_datas_carteira($carteirasInvestimento);
		//var_dump($datas_totais[0]);
		$rentabilidade_fundo = [];
		
		$busca_tipo_operacao = $this->CarteirasInvestimentos->OperacoesFinanceiras->TipoOperacoesFinanceiras->find('all');
		// saber qual tipo de operacao para somar/subtrair no patrimonio total // mudar o retorno, atual = bool
		//$busca_tipo_operacao = TableRegistry::getTableLocator()->get('TipoOperacoesFinanceiras')->find('all');
		//var_dump($busca_tipo_operacao);
		foreach ($busca_tipo_operacao as $tipo_operacao) {
			$tipo_de_operacao[$tipo_operacao['id']] = $tipo_operacao['is_aplicacao'];
		}

		// Calcula patrimonio total de uma carteira e dos fundos individuais
		$patrimonio_por_fundo = array(); //patrimonio[fundo] = valorTotalPorFundo
		$id_fundo_unique = array();	
		$patrimonio_total = [];
		$data_anterior = 0;
				
		foreach ($carteirasInvestimento->operacoes_financeiras as $operacoes) :

			$id_fundo_unique[] = $operacoes->cnpj_fundo_id;

			$auxiliar_data = (string) $operacoes->data;
			$auxiliar_fundo = $operacoes->cnpj_fundo_id;
			$valor_total = $operacoes["valor_total"];

			// se a operacao eh de deposito acrescenta, outros tipos diminui
			if ($tipo_de_operacao[$operacoes['tipo_operacoes_financeira_id']] == 1) {
				$patrimonio_total[$auxiliar_data][$auxiliar_fundo] += $valor_total;
			} else {
				$patrimonio_total[$auxiliar_data][$auxiliar_fundo] -= $valor_total;
			}
			
			//$patrimonio_por_fundo[$operacoes->cnpj_fundo_id] += $operacoes["valor_total"];

			$data_anterior = $auxiliar_data;

		endforeach;

		$id_fundo_unique = array_unique($id_fundo_unique);

		$primeira_data = $this->calcula_primeira_data($carteirasInvestimento);


		//var_dump($primeira_data);
		$aux = date('Y/m/d', $primeira_data->getTimestamp());
		//var_dump($aux);

		// busca rentabilidade
		foreach ($id_fundo_unique as $fundo_id) {

			$busca_rentabilidade = $this->CarteirasInvestimentos->OperacoesFinanceiras->CnpjFundos->DocInfDiarioFundos->find('all',
		 		['order' => ['DT_COMPTC' => 'ASC']])->where(['cnpj_fundo_id' => $fundo_id, 'DT_COMPTC >=' => $aux]);

			foreach ($busca_rentabilidade as $busca) {
				$data = date('d/m/Y', $busca['DT_COMPTC']->getTimestamp());
				$rentabilidade_fundo[$data][$fundo_id] = $busca['rentab_diaria'];
			}
		}

		//var_dump($rentabilidade_fundo);

		//var_dump($patrimonio_total);
		
		
		$patrimonio_view_fundo = [];		
		
		// pra cada fundo sem repetidos, preenche o array para exibir no grafico
		//
		$exibe = array("['Data', 'Patrimônio Líquido Total', ");
		foreach ($id_fundo_unique as $fundoId) {
			$patrimonio_view_fundo[$fundoId] = 0;
			$exibe[] = $exibe[count($exibe)-1] . "'Fundo " . (string) $fundoId . "', ";
		}
		$exibeTudo[] = $exibe[count($exibe)-1] . "],";
		//

		$patrimonio_view = [];
		$aux = 0;
		// fors pra exibir os valores no grafico patrimonio
		$data_anterior = '';
		foreach ($datas_totais as $data) {
			//$soma = 0;
			foreach ($id_fundo_unique as $fundo_id) {
				$rendimento_dia = $patrimonio_total[$data_anterior][$fundo_id] * $rentabilidade_fundo[$data][$fundoId]; // 5000 20/01/2020 * 0.0013
				$patrimonio_dia_anterior = $patrimonio_total[$data_anterior][$fundo_id] + $rendimento_dia; // 5000 19/01/2020 * 0.00005
				$patrimonio_total[$data][$fundo_id] += $patrimonio_dia_anterior; // 5000, 5000 + (5000*0.00005)
				//var_dump($rendimento_dia);
				//var_dump($patrimonio_dia_anterior);
				//var_dump($patrimonio_total[$data][$fundoId]);

				$patrimonio_view_fundo[$fundo_id] += $rendimento_dia;
				$patrimonio_view["total"] += $rendimento_dia;
				//$soma += $patrimonio_total[$data][$fundo_id];
				//$patrimonio_view_fundo[$fundoId] += $patrimonio * $rentabilidade_fundo[$data][$fundoId]; // patrimonio POR FUNDO
				//$patrimonio_view["total"] += $patrimonio_total[$data][$fundoId]; // TOTAL

			}
			//$patrimonio_view_fundo[$fundoId] += $patrimonio_view["total"];
				
			$exibeTudo[] = "['" . (string)$data . "', " . $patrimonio_view["total"];
			$tamanho = count($exibeTudo) - 1;
			foreach ($id_fundo_unique as $op) {
				// concatenar virgula e o valor do fundo na ultima linha do exibe
				$exibeTudo[$tamanho] = $exibeTudo[$tamanho] . ", " . $patrimonio_view_fundo[$op];
			}
			$exibeTudo[$tamanho] = $exibeTudo[$tamanho] . "],";

			//$patrimonio_total[$data]['Total'] += $soma;
			$data_anterior = $data;
			//var_dump($data_anterior);
		}

		
		
		$this->set(compact('exibe', 'exibe2', 'id_fundo_unique', 'exibeTudo'));
		
		

		
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
