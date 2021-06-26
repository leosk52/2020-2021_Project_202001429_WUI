<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DistribuidorFundos Model
 *
 * @property \App\Model\Table\OperacoesFinanceirasTable&\Cake\ORM\Association\HasMany $OperacoesFinanceiras
 *
 * @method \App\Model\Entity\DistribuidorFundo newEmptyEntity()
 * @method \App\Model\Entity\DistribuidorFundo newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DistribuidorFundo[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DistribuidorFundo get($primaryKey, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DistribuidorFundo[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DistribuidorFundo|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DistribuidorFundo[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DistribuidorFundosTable extends Table
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

        $this->setTable('distribuidor_fundos');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->hasMany('OperacoesFinanceiras', [
            'foreignKey' => 'distribuidor_fundo_id',
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
            ->scalar('cnpj')
            ->maxLength('cnpj', 20)
            ->requirePresence('cnpj', 'create')
            ->notEmptyString('cnpj')
            ->add('cnpj', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('nome')
            ->maxLength('nome', 100)
            ->requirePresence('nome', 'create')
            ->notEmptyString('nome');

        $validator
            ->date('DT_REG_CVM')
            ->requirePresence('DT_REG_CVM', 'create')
            ->notEmptyDate('DT_REG_CVM');

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
        $rules->add($rules->isUnique(['cnpj']));

        return $rules;
    }
}
