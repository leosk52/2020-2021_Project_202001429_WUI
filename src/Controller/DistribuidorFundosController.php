<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * DistribuidorFundos Controller
 *
 * @property \App\Model\Table\DistribuidorFundosTable $DistribuidorFundos
 * @method \App\Model\Entity\DistribuidorFundo[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DistribuidorFundosController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $distribuidorFundos = $this->paginate($this->DistribuidorFundos);

        $this->set(compact('distribuidorFundos'));
    }

    /**
     * View method
     *
     * @param string|null $id Distribuidor Fundo id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $distribuidorFundo = $this->DistribuidorFundos->get($id, [
            'contain' => ['OperacoesFinanceiras'],
        ]);

        $this->set(compact('distribuidorFundo'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $distribuidorFundo = $this->DistribuidorFundos->newEmptyEntity();
        if ($this->request->is('post')) {
            $distribuidorFundo = $this->DistribuidorFundos->patchEntity($distribuidorFundo, $this->request->getData());
            if ($this->DistribuidorFundos->save($distribuidorFundo)) {
                $this->Flash->success(__('The distribuidor fundo has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The distribuidor fundo could not be saved. Please, try again.'));
        }
        $this->set(compact('distribuidorFundo'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Distribuidor Fundo id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $distribuidorFundo = $this->DistribuidorFundos->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $distribuidorFundo = $this->DistribuidorFundos->patchEntity($distribuidorFundo, $this->request->getData());
            if ($this->DistribuidorFundos->save($distribuidorFundo)) {
                $this->Flash->success(__('The distribuidor fundo has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The distribuidor fundo could not be saved. Please, try again.'));
        }
        $this->set(compact('distribuidorFundo'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Distribuidor Fundo id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $distribuidorFundo = $this->DistribuidorFundos->get($id);
        if ($this->DistribuidorFundos->delete($distribuidorFundo)) {
            $this->Flash->success(__('The distribuidor fundo has been deleted.'));
        } else {
            $this->Flash->error(__('The distribuidor fundo could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function ajaxBuscaPorNome()
    {
        $this->request->allowMethod('ajax');
        $DistribuidorFundos = $this->getTableLocator()->get('DistribuidorFundos');
        $keyword = $this->request->getQuery('keyword');
        if ($keyword != '') {
            $query = $DistribuidorFundos->find('all', [
                'conditions' => ['nome LIKE' => '%' . $keyword . '%'],
                'order' => ['CNPJ' => 'DESC'],
                'limit' => 100
            ]);
        } else {
            $id = $this->request->getQuery('id');
            $query = $DistribuidorFundos->find('all', [
                'conditions' => ['id' => $id]
            ]);
        }

        $this->set(['distribuidores_encontrados' => $this->paginate($query)]);
        $this->viewBuilder()->setOption('serialize', true);
        $this->RequestHandler->renderAs($this, 'json');
    }
}
