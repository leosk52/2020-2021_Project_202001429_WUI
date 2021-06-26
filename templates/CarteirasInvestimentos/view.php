<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CarteirasInvestimento $carteirasInvestimento
 */
?>
<div class="row">
    <!--<aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
	<?= $this->Html->link(__('Edit Carteiras Investimento'), ['action' => 'edit', $carteirasInvestimento->id], ['class' => 'side-nav-item']) ?>
	<?= $this->Form->postLink(__('Delete Carteiras Investimento'), ['action' => 'delete', $carteirasInvestimento->id], ['confirm' => __('Are you sure you want to delete # {0}?', $carteirasInvestimento->id), 'class' => 'side-nav-item']) ?>
	<?= $this->Html->link(__('List Carteiras Investimentos'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
	<?= $this->Html->link(__('New Carteiras Investimento'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>-->
    <div class="column-responsive">
        <div class="carteirasInvestimentos view content">
            <!--<h3><?= h($carteirasInvestimento->id) ?></h3>-->
            <table>
                <!--<tr>
                    <th><?= __('Usuario') ?></th>
                    <td><?= $carteirasInvestimento->has('usuario') ? $this->Html->link($carteirasInvestimento->usuario->nome, ['controller' => 'Usuarios', 'action' => 'view', $carteirasInvestimento->usuario->id]) : '' ?></td>
                </tr>-->
                <tr>
                    <th><?= __('Nome') ?></th>
                    <td><?= h($carteirasInvestimento->nome) ?></td>
                </tr>
                <tr>
                    <th><?= __('Descricao') ?></th>
                    <td><?= h($carteirasInvestimento->descricao) ?></td>
                </tr>
                <!--<tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($carteirasInvestimento->id) ?></td>
                </tr>-->
            </table>

            <div class="related">
                <h4><?= __('Operações Financeiras (Transações)') ?></h4>
                <?= $this->Html->link(__('Nova Operação Financeira'), ['controller' => 'OperacoesFinanceiras', 'action' => 'add', $carteirasInvestimento->id], ['class' => 'button float-right']) ?>

                <?php if (!empty($operacoesFinanceiras)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __('Fundo') ?></th>
                                <th><?= __('Distribuidor Fundo') ?></th>
                                <th><?= __('Operação Financeira') ?></th>
                                <th><?= __('Valor Total') ?></th>
                                <th><?= __('Valor Cota') ?></th>
                                <th><?= __('Quantidade Cotas') ?></th>
                                <th><?= __('Data') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($operacoesFinanceiras as $operacoesFinanceira) : ?>
                                <tr>
                                    <td><?= h($operacoesFinanceira->cnpj_fundo->DENOM_SOCIAL) ?></td>
                                    <td><?= h($operacoesFinanceira->distribuidor_fundo->nome) ?></td>
                                    <td><?= h($operacoesFinanceira->tipo_operacoes_financeira->nome) ?></td>
                                    <td><?= h($operacoesFinanceira->valor_total) ?></td>
                                    <td><?= h($operacoesFinanceira->valor_cota) ?></td>
                                    <td><?= h($operacoesFinanceira->quantidade_cotas) ?></td>
                                    <td><?= h($operacoesFinanceira->data) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'OperacoesFinanceiras', 'action' => 'view', $operacoesFinanceira->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'OperacoesFinanceiras', 'action' => 'edit', $operacoesFinanceira->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'OperacoesFinanceiras', 'action' => 'delete', $operacoesFinanceira->id], ['confirm' => __('Are you sure you want to delete # {0}?', $operacoesFinanceira->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    <div class="paginator">
                        <ul class="pagination">
                            <?= $this->Paginator->first('<< ' . __('first')) ?>
                            <?= $this->Paginator->prev('< ' . __('previous')) ?>
                            <?= $this->Paginator->numbers() ?>
                            <?= $this->Paginator->next(__('next') . ' >') ?>
                            <?= $this->Paginator->last(__('last') . ' >>') ?>
                        </ul>
                        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="related">
                <h4><?= __('Indicadores Financeiros da Carteira') ?></h4>
                <?php if (!empty($carteirasInvestimento->indicadores_carteiras)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __('Periodo Meses') ?></th>
                                <th><?= __('Data Final') ?></th>
                                <th><?= __('Rentabilidade') ?></th>
                                <th><?= __('Desvio Padrao') ?></th>
                                <th><?= __('Num Valores') ?></th>
                                <th><?= __('Rentab Min') ?></th>
                                <th><?= __('Rentab Max') ?></th>
                                <th><?= __('Max Drawdown') ?></th>
                                <th><?= __('Benchmark') ?></th>
                                <th><?= __('Meses Acima Bench') ?></th>
                                <th><?= __('Sharpe') ?></th>
                                <th><?= __('Beta') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($carteirasInvestimento->indicadores_carteiras as $indicadoresCarteiras) : ?>
                                <tr>
                                    <td><?= h($indicadoresCarteiras->periodo_meses) ?></td>
                                    <td><?= h($indicadoresCarteiras->data_final) ?></td>
                                    <td><?= h($indicadoresCarteiras->rentabilidade) ?></td>
                                    <td><?= h($indicadoresCarteiras->desvio_padrao) ?></td>
                                    <td><?= h($indicadoresCarteiras->num_valores) ?></td>
                                    <td><?= h($indicadoresCarteiras->rentab_min) ?></td>
                                    <td><?= h($indicadoresCarteiras->rentab_max) ?></td>
                                    <td><?= h($indicadoresCarteiras->max_drawdown) ?></td>
                                    <td><?= h($indicadoresCarteiras->tipo_benchmark_id) ?></td>
                                    <td><?= h($indicadoresCarteiras->meses_acima_bench) ?></td>
                                    <td><?= h($indicadoresCarteiras->sharpe) ?></td>
                                    <td><?= h($indicadoresCarteiras->beta) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'IndicadoresCarteiras', 'action' => 'view', $indicadoresCarteiras->carteiras_investimento_id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'IndicadoresCarteiras', 'action' => 'edit', $indicadoresCarteiras->carteiras_investimento_id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'IndicadoresCarteiras', 'action' => 'delete', $indicadoresCarteiras->carteiras_investimento_id], ['confirm' => __('Are you sure you want to delete # {0}?', $indicadoresCarteiras->carteiras_investimento_id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
