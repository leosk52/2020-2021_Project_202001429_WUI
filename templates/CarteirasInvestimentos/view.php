<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CarteirasInvestimento $carteirasInvestimento
 */

use Phinx\Db\Action\AddColumn;

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
			
			
			<?php if (!empty($carteirasInvestimento->operacoes_financeiras)) : ?>
            <div class="related">
                <h4><?= __('Indicadores Financeiros da Carteira') ?></h4>>
					<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

					<div class="row">
						<div class="column-graph">
							<?php
							echo$this->element('titleInfo', array('title' => __('Patrimônio Da Carteira e dos Ativos'), 'align' => 'center', 'h' => 3));
						
							$exibe = array("['Data', 'Patrimônio Líquido Total', ");
							foreach ($id_fundo_unique as $fundo_id) {
								$exibe[] = $exibe[count($exibe)-1] . "'Fundo " . (string) $fundo_id . "', ";
							}
							$exibeTudo[] = $exibe[count($exibe)-1] . "],";
							
							foreach ($datas_totais as $data) {
								$exibeTudo[] = "['" . (string)$data . "', " . $calculo_patrimonio[$data]["total"];
								$tamanho = count($exibeTudo) - 1;
								foreach ($id_fundo_unique as $fundo_id) {
									$exibeTudo[$tamanho] = $exibeTudo[$tamanho] . ", " . $calculo_patrimonio[$data][$fundo_id];
								}
								$exibeTudo[$tamanho] = $exibeTudo[$tamanho] . "],";
							}

							echo$this->element('googleChartFundo', array('data' => $exibeTudo, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'currency', 'chart' => 'chart1_div'));
							?>
							<div id="chart1_div" style="width: 100%; height: 400px;"></div>
						</div>
						
						<div class="column-graph"'>
							<?php
								echo$this->element('titleInfo', array('title' => __('Drawdown'), 'align' => 'center', 'h' => 3));							
								$exibe1 = array("['Data', 'Drawdown Total'], ");

								foreach ($datas_totais as $data) {
									$exibe1[] = "['" . (string)$data . "', " . -$calculo_drawdown[$data]["total"] . "],";
								}
								
							echo$this->element('googleChartFundo', array('data' => $exibe1, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'chart' => 'chart2_div'));
							?>
							<div id="chart2_div" style="width: 100%; height: 400px;"></div>
						</div>
					</div>		

					<div class="row">
						<div class="column-graph">
							<?php
							echo$this->element('titleInfo', array('title' => __('Rentabilidade Da Carteira e dos Ativos'), 'align' => 'center', 'h' => 3));
						
							$exibe2 = array("['Data', 'Rentabilidade Acumulada', ");
							foreach ($id_fundo_unique as $fundo_id) {
								$exibe2[] = $exibe2[count($exibe2)-1] . "'Rentabilidade diária do Fundo " . (string) $fundo_id . "', ";
							}
							$exibeTudo2[] = $exibe2[count($exibe2)-1] . "],";
							
							foreach ($datas_totais as $data) {
								$exibeTudo2[] = "['" . (string)$data . "', " . $calculo_rentab_percent[$data]["total"];
								$tamanho = count($exibeTudo2) - 1;
								foreach ($id_fundo_unique as $fundo_id) {
									$exibeTudo2[$tamanho] = $exibeTudo2[$tamanho] . ", " . $calculo_rentab_percent[$data][$fundo_id];
								}
								$exibeTudo2[$tamanho] = $exibeTudo2[$tamanho] . "],";
							}

							echo$this->element('googleChartFundo', array('data' => $exibeTudo2, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'chart' => 'chart3_div'));
							?>
							<div id="chart3_div" style="width: 100%; height: 400px;"></div>
						</div>
					</div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
