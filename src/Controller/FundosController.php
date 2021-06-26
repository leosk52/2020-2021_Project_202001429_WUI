<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;
use \Cake\Event\EventInterface;
use Cake\View\ViewBuilder;
use App\Form\FundosFiltroForm;

/**
 * Fundos Controller
 *
 */
class FundosController extends AppController
{

    //var $helpers = array('Html', 'Ajax', 'Javascript', 'Form');
    // In a controller or table method.

    public $paginate = array(
        'maxLimit' => 20, //Registros por página
        //		'limit' => 100, //Registros por consulta
        'paramType' => 'querystring' //Esta linha analisa o parâmetro fornecido pelo link.
    );

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        if ($this->request->is('ajax')) {
            //	$this->viewBuilder()->setLayout('ajax');
        }
    }

    public function index()
    {
        $this->viewBuilder()->setHelpers(['Html', 'Ajax', 'Javascript', 'Form']);
        $this->redirect('/Fundos/busca');
    }

    function buscaFundoPorAssociacaoCampo($associacao, $campo, $valor, \Cake\ORM\Query $queryFundos)
    {
        $novaQuery = clone $queryFundos;
        $novaQuery->matching($associacao, function ($q) {
            return $q->where([$campo . ' LIKE' => '%' . $valor . '%']);
        });
        if ($novaQuery->count() > 0) {
            return $novaQuery;
        }
        $novaQuery = clone $queryFundos;
        $novaQuery->matching($associacao, function ($q) {
            return $q->where([$campo . ' LIKE' => '% ' . $valor . ' %']);
        });
        if ($novaQuery->count() > 0) {
            return $novaQuery;
        }
        $novaQuery = clone $queryFundos;
        $partesNomeBuscado = str_replace(' ', '%', $valor);
        $novaQuery->matching($associacao, function ($q) {
            return $q->where([$campo . ' LIKE' => '% ' . $partesNomeBuscado . ' %']);
        });

        return $novaQuery;
    }

    function buscaFundoPorCampo($campo, $valor, \Cake\ORM\Query $queryFundos)
    {
        $novaQuery = clone $queryFundos;
        $novaQuery->andWhere([$campo . ' LIKE' => '% ' . $valor . ' %']);
        if ($novaQuery->count() > 0) {
            return $novaQuery;
        }
        $novaQuery = clone $queryFundos;
        $novaQuery->andWhere([$campo . ' LIKE' => '%' . $valor . '%']);
        if ($novaQuery->count() > 0) {
            return $novaQuery;
        }
        $novaQuery = clone $queryFundos;
        $partesNomeBuscado = str_replace(' ', '%', $valor);
        $novaQuery->andWhere([$campo . ' LIKE' => '%' . $partesNomeBuscado . '%']);

        return $novaQuery;
    }

    /**
     * Busca method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function busca()
    {
        /*
		  $ClassesFundos = TableRegistry::getTableLocator()->get('TipoClasseFundos');
		  $classeFundos = $ClassesFundos->find();
		  $this->set(compact('classeFundos'));
		  //
		  $ClassesAnbima = TableRegistry::getTableLocator()->get('TipoAnbimaClasses');
		  $classeAnbima = $ClassesAnbima->find();
		  $this->set(compact('classeAnbima'));
		 */

        //
        //
        // lista
        $CnpjFundos = $this->getTableLocator()->get('CnpjFundos');
        $queryFundos = $CnpjFundos->find()
            ->select(['id', 'CNPJ', 'DENOM_SOCIAL'])
            ->distinct(['CnpjFundos.id'])
            ->contain(
                [
                    'CadastroFundos' => [
                        'sort' => ['CadastroFundos.DT_REG_CVM ' => 'DESC'],
                        'TipoClasseFundos',
                        'TipoRentabilidadeFundos',
                        'AdministradorFundos'
                    ]
                ]
            )
            ->contain([
                'DocExtratosFundos' => [
                    'sort' => ['DocExtratosFundos.DT_COMPTC' => 'DESC'],
                    'TipoAnbimaClasses'
                ]
            ])
            ->contain([
                'SituacaoFundos' => [
                    'sort' => ['SituacaoFundos.DT_INI_SIT' => 'DESC'],
                    'TipoSituacaoFundos'
                ]
            ])
            ->notMatching('CancelamentoFundos')
            ->matching('CadastroFundos', function ($q) {
                return $q->where(['CadastroFundos.condom_aberto' => 1]); // 1 = Fundos abertos (condomínio aberto)
            })
            //->matching('SituacaoFundos', function ($q) {
            //	return $q->where(['SituacaoFundos.tipo_situacao_fundo_id' => 1]); // 1 = EM FUNCIONAMENTO NORMAL
            //}) //  TODO Não funciona, pois fundo pode ter duas situações. Uma em funcionamento e depois outra, cancelando. Esse matching não exclui fundos com situação cancelada
            ->where([
                ['CnpjFundos.DENOM_SOCIAL != ' => ' --DESCONHECIDO--'],
            ]) // OUTROS FILTROS
            ->order(['CnpjFundos.DENOM_SOCIAL' => 'asc'])
            /*
		  // OUTROS FILTROS
		  /*
		  ->matching('CadastroFundos', function ($q) {
		  return $q->where(['CadastroFundos.tipo_classe_fundo_id' => 3]);
		  })
		  ->matching('DocExtratosFundos', function ($q) {
		  return $q->where(['DocExtratosFundos.tipo_anbima_classe_id' => 3]);
		  })
		  ->matching('CadastroFundos.AdministradorFundos', function ($q) {
		  return $q->where(['AdministradorFundos.nome LIKE' => '%PACTUAL%']);
		  })
		 */;
        //
        // filtro
        $formFiltro = new FundosFiltroForm();
        $divResultados = '';
        if ($this->request->is('post')) {
            //if ($formFiltro->execute()) {
            //	$this->Flash->success('We will get back to you soon.');
            //} else {
            //	$this->Flash->error('There was a problem submitting your form.');
            //}
            $nomeBuscado = $this->request->getData('nome');
            if ($nomeBuscado != '') {
                $queryFundos = $this->buscaFundoPorCampo('DENOM_SOCIAL', $nomeBuscado, $queryFundos);
                /*
				  $divResultados = '<div class="dropdown-visible-content"> Resultados:<br/>';
				  foreach ($queryFundos as $result) {
				  $divResultados .= '<a>' . $result['DENOM_SOCIAL'] . '</a ><br/>';
				  }
				  $divResultados .= '</div>';
				  echo($divResultados);
				  exit();
				 */
            }
            $admBuscado = $this->request->getData('administrador');
            if ($admBuscado != '') {
                $queryFundos = $this->buscaFundoPorAssociacaoCampo('CadastroFundos.AdministradorFundos', 'AdministradorFundos.nome', $admBuscado, $queryFundos);
            }
        }
        //if ($this->request->is('get')) {
        //}
        $this->set('divResultados', ''); /////$divResultados);
        $this->set('formFiltro', $formFiltro);

        $cnpjFundos = $this->paginate($queryFundos);
        $this->set(compact('cnpjFundos'));
        // Specify which view vars JsonView should serialize.
        //$this->viewBuilder()->setOption('serialize', 'cnpjFundos', ['classeAnbima', 'classeFundos']);
    }

    /**
     * View method
     *
     * @param string|null $id Cnpj Fundo id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $CnpjFundos = TableRegistry::getTableLocator()->get('CnpjFundos');
        $cnpjFundo = $CnpjFundos->get($id, [
            'contain' => [
                'DocInfDiarioFundos' => ['sort' => ['DocInfDiarioFundos.DT_COMPTC' => 'ASC']], 'CancelamentoFundos', 'CadastroFundos' => ['sort' => ['CadastroFundos.DT_REG_CVM ' => 'DESC'], 'TipoClasseFundos', 'TipoRentabilidadeFundos', 'GestorFundos', 'AdministradorFundos'],
                'DocExtratosFundos' => ['TipoAnbimaClasses'],
                'SituacaoFundos' => ['sort' => ['SituacaoFundos.DT_INI_SIT' => 'DESC'], 'TipoSituacaoFundos']
            ],
        ]);
        $this->set(compact('cnpjFundo'));
        //
        $IndicadoresFundos = TableRegistry::getTableLocator()->get('IndicadoresFundos');
        $indicadores = $IndicadoresFundos->find()->where(['cnpj_fundo_id' => $id]);
        $this->set(compact('indicadores'));
    }

    /**
     * Comparacao method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function comparacao()
    {
        $IndicadoresFundos = TableRegistry::getTableLocator()->get('IndicadoresFundos');
        //$this->paginate = [
        //	'contain' => ['CnpjFundos' => ['CadastroFundos' => ['TipoClasseFundos']]],
        //];
        $indicadores = $this->paginate($IndicadoresFundos);
        $this->set(compact('indicadores'));
    }

    /**
     * Comparacao method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function indicadores()
    {
    }

    public function ajaxBuscaPorNome()
    {
        $this->request->allowMethod('ajax');
        $CnpjFundos = $this->getTableLocator()->get('CnpjFundos');
        $keyword = $this->request->getQuery('keyword');
        if ($keyword != '') {
            $query = $CnpjFundos->find('all', [
                'conditions' => ['DENOM_SOCIAL LIKE' => '%' . $keyword . '%'],
                'order' => ['CNPJ' => 'DESC'],
                'limit' => 100
            ]);
        } else {
            $id = $this->request->getQuery('id');
            $query = $CnpjFundos->find('all', [
                'conditions' => ['id' => $id]
            ]);
        }

        $this->set(['fundos_encontrados' => $this->paginate($query)]);
        $this->viewBuilder()->setOption('serialize', true);
        $this->RequestHandler->renderAs($this, 'json');
    }
}
