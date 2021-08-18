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
    <div class="column-responsive column-80">
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
				<?= $this->Html->link(__('Nova Operação Financeira'), ['controller'=>'OperacoesFinanceiras', 'action' => 'add'], ['class' => 'button float-right']) ?>

				<?php if (!empty($carteirasInvestimento->operacoes_financeiras)) : ?>
	                <div class="table-responsive">
	                    <table>
	                        <tr>
	                            <th><?= __('Id') ?></th>
	                            <th><?= __('Carteiras Investimento Id') ?></th>
	                            <th><?= __('Cnpj Fundo Id') ?></th>
	                            <th><?= __('Distribuidor Fundo Id') ?></th>
	                            <th><?= __('Tipo Operacoes Financeira Id') ?></th>
	                            <th><?= __('Por Valor') ?></th>
	                            <th><?= __('Valor Total') ?></th>
	                            <th><?= __('Valor Cota') ?></th>
	                            <th><?= __('Quantidade Cotas') ?></th>
	                            <th><?= __('Data') ?></th>
	                            <th class="actions"><?= __('Actions') ?></th>
	                        </tr>
							<?php foreach ($carteirasInvestimento->operacoes_financeiras as $operacoesFinanceiras) : ?>
		                        <tr>
		                            <td><?= h($operacoesFinanceiras->id) ?></td>
		                            <td><?= h($operacoesFinanceiras->carteiras_investimento_id) ?></td>
		                            <td><?= h($operacoesFinanceiras->cnpj_fundo_id) ?></td>
		                            <td><?= h($operacoesFinanceiras->distribuidor_fundo_id) ?></td>
		                            <td><?= h($operacoesFinanceiras->tipo_operacoes_financeira_id) ?></td>
		                            <td><?= h($operacoesFinanceiras->por_valor) ?></td>
		                            <td><?= h($operacoesFinanceiras->valor_total) ?></td>
		                            <td><?= h($operacoesFinanceiras->valor_cota) ?></td>
		                            <td><?= h($operacoesFinanceiras->quantidade_cotas) ?></td>
		                            <td><?= h($operacoesFinanceiras->data) ?></td>
		                            <td class="actions">
										<?= $this->Html->link(__('View'), ['controller' => 'OperacoesFinanceiras', 'action' => 'view', $operacoesFinanceiras->id]) ?>
										<?= $this->Html->link(__('Edit'), ['controller' => 'OperacoesFinanceiras', 'action' => 'edit', $operacoesFinanceiras->id]) ?>
										<?= $this->Form->postLink(__('Delete'), ['controller' => 'OperacoesFinanceiras', 'action' => 'delete', $operacoesFinanceiras->id], ['confirm' => __('Are you sure you want to delete # {0}?', $operacoesFinanceiras->id)]) ?>
		                            </td>
		                        </tr>
							<?php endforeach; ?>
	                    </table>
	                </div>
				<?php endif; ?>
            </div>
			
				<table>
				<div class="related">
				<h3><?= __('Gráficos') ?></h3>
				<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

				<div class="row">
					<div class="column-graph">
						<?php
						echo$this->element('titleInfo', array('title' => __('Patrimônio Líquido'), 'align' => 'center', 'tam' => 4, 'info' => __('O patrimônio líquido é 
						a quantidade de recursos...')));
						
						
						echo$this->element('googleChartFundo', array('data' => $exibe, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'currency', 'chart' => 'chart1_div'));
						//echo var_dump($valorReal);	
						?>
						<div id="chart1_div" style="width: 100%; height: 400px;"></div>
					</div>
				</div>
				</table>
			</div>

			
            <div class="related">
                <h4><?= __('Indicadores Financeiros da Carteira') ?></h4>
				<?php if (!empty($carteirasInvestimento->indicadores_carteiras)) : ?>
	                <div class="table-responsive">
	                    <table>
	                        <tr>
	                            <th><?= __('Carteiras Investimento Id') ?></th>
	                            <th><?= __('Periodo Meses') ?></th>
	                            <th><?= __('Data Final') ?></th>
	                            <th><?= __('Rentabilidade') ?></th>
	                            <th><?= __('Desvio Padrao') ?></th>
	                            <th><?= __('Num Valores') ?></th>
	                            <th><?= __('Rentab Min') ?></th>
	                            <th><?= __('Rentab Max') ?></th>
	                            <th><?= __('Max Drawdown') ?></th>
	                            <th><?= __('Tipo Benchmark Id') ?></th>
	                            <th><?= __('Meses Acima Bench') ?></th>
	                            <th><?= __('Sharpe') ?></th>
	                            <th><?= __('Beta') ?></th>
	                            <th class="actions"><?= __('Actions') ?></th>
	                        </tr>
							<?php foreach ($carteirasInvestimento->indicadores_carteiras as $indicadoresCarteiras) : ?>
		                        <tr>
		                            <td><?= h($indicadoresCarteiras->carteiras_investimento_id) ?></td>
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
