<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * CarteirasInvestimentos Controller
 *
 * @property \App\Model\Table\CarteirasInvestimentosTable $CarteirasInvestimentos
 * @method \App\Model\Entity\CarteirasInvestimento[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CarteirasInvestimentosController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
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
    public function view($id = null)
    {
        $carteirasInvestimento = $this->CarteirasInvestimentos->get($id, [
            'contain' => [
                'Usuarios', 'IndicadoresCarteiras',
            ],
        ]);

        $operacoesFinanceirasQuery = $this->CarteirasInvestimentos->OperacoesFinanceiras
            ->find('all', [
                'contain' => ['CnpjFundos', 'DistribuidorFundos', 'TipoOperacoesFinanceiras'],
                'conditions' => [
                    'OperacoesFinanceiras.carteiras_investimento_id' => $carteirasInvestimento->id
                ],
                'order' => ['data' => 'DESC']
            ]);

        $paginationOptions = [
            'limit' => 5,
        ];

        $operacoesFinanceiras = $this->paginate($operacoesFinanceirasQuery, $paginationOptions);

        $this->set(compact('carteirasInvestimento', 'operacoesFinanceiras'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
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
    public function edit($id = null)
    {
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
    public function delete($id = null)
    {
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

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $session = $this->request->getSession();
        $conectado = $session->read('User.id') != null;
        if (!$conectado) {
            $this->Flash->error(__('Você precisa estar logado para acessar a página solicitada. Você foi redirecionado à página principal.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'home']);
        }
    }
}
