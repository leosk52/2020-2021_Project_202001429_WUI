<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OperacoesFinanceiras Model
 *
 * @property \App\Model\Table\CarteirasInvestimentosTable&\Cake\ORM\Association\BelongsTo $CarteirasInvestimentos
 * @property \App\Model\Table\CnpjFundosTable&\Cake\ORM\Association\BelongsTo $CnpjFundos
 * @property \App\Model\Table\DistribuidorFundosTable&\Cake\ORM\Association\BelongsTo $DistribuidorFundos
 * @property \App\Model\Table\TipoOperacoesFinanceirasTable&\Cake\ORM\Association\BelongsTo $TipoOperacoesFinanceiras
 *
 * @method \App\Model\Entity\OperacoesFinanceira newEmptyEntity()
 * @method \App\Model\Entity\OperacoesFinanceira newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira get($primaryKey, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OperacoesFinanceira[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class OperacoesFinanceirasTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('operacoes_financeiras');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('CarteirasInvestimentos', [
            'foreignKey' => 'carteiras_investimento_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CnpjFundos', [
            'foreignKey' => 'cnpj_fundo_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('DistribuidorFundos', [
            'foreignKey' => 'distribuidor_fundo_id',
        ]);
        $this->belongsTo('TipoOperacoesFinanceiras', [
            'foreignKey' => 'tipo_operacoes_financeira_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('RelCarteirasOperacoes', [
            'foreignKey' => 'operacoes_financeira_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->boolean('por_valor')
            ->requirePresence('por_valor', 'create')
            ->notEmptyString('por_valor');

        $validator
            ->numeric('valor_total')
            ->requirePresence('valor_total', 'create')
            ->notEmptyString('valor_total');

        $validator
            ->numeric('valor_cota')
            ->requirePresence('valor_cota', 'create')
            ->notEmptyString('valor_cota');

        $validator
            ->integer('quantidade_cotas')
            ->requirePresence('quantidade_cotas', 'create')
            ->notEmptyString('quantidade_cotas');

        $validator
            ->date('data')
            ->requirePresence('data', 'create')
            ->notEmptyDate('data');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['carteiras_investimento_id'], 'CarteirasInvestimentos'), ['errorField' => 'carteiras_investimento_id']);
        $rules->add($rules->existsIn(['cnpj_fundo_id'], 'CnpjFundos'), ['errorField' => 'cnpj_fundo_id']);
        $rules->add($rules->existsIn(['distribuidor_fundo_id'], 'DistribuidorFundos'), ['errorField' => 'distribuidor_fundo_id']);
        $rules->add($rules->existsIn(['tipo_operacoes_financeira_id'], 'TipoOperacoesFinanceiras'), ['errorField' => 'tipo_operacoes_financeira_id']);

        return $rules;
    }
}
