<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CarteirasInvestimento $carteirasInvestimento
 */
?>

<head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<div class="row">
    <div class="column-responsive">
        <div class="carteirasInvestimentos view content">
            <?php echo $this->element('titleInfo', array('title' => h(__('Análise da carteira')), 'h' => 1)); ?>
            <table>
                <tr>
                    <th><?= __('Nome') ?></th>
                    <td><?= h($carteirasInvestimento->nome) ?></td>
                </tr>
                <tr>
                    <th><?= __('Descrição') ?></th>
                    <td><?= h($carteirasInvestimento->descricao) ?></td>
                </tr>
                <tr>
                    <th><?= __('Data de início') ?></th>
                    <td><?= h($carteirasInvestimento->data_inicio) ?></td>
                </tr>
                <tr>
                    <th><?= __('Saldo total') ?></th>
                    <td><?= h($carteirasInvestimento->saldo_total) ?></td>
                </tr>
            </table>

            <div id="linechart"></div>

            <div class="related">
                <h4><?= __('Posição') ?></h4>
                <?php if (!empty($fundos)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __('Fundo') ?></th>
                                <th><?= __('Saldo Anterior') ?></th>
                                <th><?= __('Saldo Atual') ?></th>
                                <th><?= __('Participação %') ?></th>
                            </tr>
                            <?php foreach ($fundos as $fundo) : ?>
                                <tr>
                                    <td><?= h($fundo['nome']) ?></td>
                                    <td><?= h($fundo['saldo_anterior']) ?></td>
                                    <td><?= h($fundo['saldo_atual']) ?></td>
                                    <td><?= h($fundo['participacao']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="related">
                <h3><?= __('Gráficos') ?></h3>

                <div class="row">
                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Saldo'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
                        $data = array();
                        $data[] = "['Data', 'Aplicação'],";
                        foreach ($aplicacaoPorMes as $data_aplicacao => $valor_aplicado) {
                            $data[] = "['" . $data_aplicacao . "'," . $valor_aplicado . "],";
                        }

                        echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'currency', 'hAxisTitle' => '', 'chart' => 'chart1_div'));
                        ?>
                        <div id="chart1_div" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Rentabilidade Acumulada'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
                        $data = array();
                        $data[] = "['Data', 'Acumulada', 'Rentabilidade'],";
                        $rentabilidadeAcumulada = 0;
                        foreach ($indicadores as $indicador) {
                            $rentabilidadeAcumulada += $indicador->rentabilidade;
                            $data[] = "['" . $indicador->data_final->format('M Y') . "'," . $rentabilidadeAcumulada . "," . $indicador->rentabilidade . "],";
                        }
                        echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'chart' => 'chart7_div'));
                        ?>
                        <div id="chart7_div" style="width: 100%; height: 400px;"></div>
                    </div>

                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Drawdown'), 'align' => 'center', 'h' => 3, 'info' => __('')));
                        $data = array();
                        $data[] = "['Data', 'Drawdown'],";
                        foreach ($indicadores as $indicador) {
                            $data[] = "['" . $indicador->data_final->format('M Y')  . "'," . $indicador['max_drawdown'] . "],";
                        }
                        echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'chart' => 'chart8_div'));
                        ?>
                        <div id="chart8_div" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Volatilidade (Risco)'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
                        $data = array();
                        $data[] = "['Data', 'Volatilidade'],";
                        foreach ($indicadores as $indicador) {
                            $data[] = "['" . $indicador->data_final->format('M Y') . "'," . $indicador->desvio_padrao . "],";
                        }

                        echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'hAxisTitle' => '', 'chart' => 'chart2_div'));
                        ?>
                        <div id="chart2_div" style="width: 100%; height: 400px;"></div>
                    </div>

                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Retorno x Risco'), 'align' => 'center', 'h' => 3, 'info' => __('A relação entre o retorno esperado e o risco de um investimento fé um indicador que permite avaliar se um fundo "vale a pena" ou não. Riscos mais altos devem ser recompensados com um retorno maior. Se o aumento do risco não estiver associado a um retorno maior, o investimento pode não ser interessante...')));
                        $data = array();
                        $data[] = "['ID', 'Risco', 'Retorno'],";
                        foreach ($indicadores as $indicador) {
                            $data[] = "['" . $indicador->data_final->format('M Y') . "'," . $indicador['desvio_padrao'] . "," . $indicador['rentabilidade'] . "],";
                        }
                        echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => 'Retorno', 'vAxisFormat' => 'percent', 'hAxisTitle' => 'Risco', 'hAxisFormat' => 'percent', 'chartType' => 'Bubble', 'chart' => 'chart6_div'));
                        ?>
                        <div id="chart6_div" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>


                <div class="row">
                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Alocação por ativo'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
                        $data = array();
                        $data[] = "['Fundo', 'Alocação'],";
                        foreach ($alocacoesPorAtivo as $fundo => $alocacao) {
                            $data[] = "['" . $fundo . "'," . $alocacao / $aplicacaoTotal . "],";
                        }

                        echo $this->element('googleChartFundo', array('chartType' => 'Pie', 'data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'hAxisTitle' => '', 'chart' => 'chart3_div'));
                        ?>
                        <div id="chart3_div" style="width: 100%; height: 400px;"></div>
                    </div>

                    <div class="column-graph">
                        <?php
                        echo $this->element('titleInfo', array('title' => __('Alocação por classe'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
                        $data = array();
                        $data[] = "['Classe', 'Alocação'],";
                        foreach ($alocacoesPorClasse as $fundo => $alocacao) {
                            $data[] = "['" . $fundo . "'," . $alocacao / $aplicacaoTotal . "],";
                        }

                        echo $this->element('googleChartFundo', array('chartType' => 'Pie', 'data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'hAxisTitle' => '', 'chart' => 'chart4_div'));
                        ?>
                        <div id="chart4_div" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
