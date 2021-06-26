<?php

/**
 *
 */
?>

<head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<script>
    $('document').ready(function() {
        $('#nome-busca-carteiras').keyup(function() {
            var searchkey = $(this).val();
            buscaCarteiras(searchkey);
        });

        function buscaCarteiras(keyword) {
            var data = keyword;
            $.ajax({
                method: 'get',
                url: "<?php echo $this->Url->build(['controller' => 'Portfolio', 'action' => 'buscaCarteiras']); ?>",
                data: {
                    keyword: data
                },

                success: function(response) {
                    $('#resultado-busca-carteiras').html(response);
                }
            });
        };
    });

    function removeCarteira(id) {
        const urlParams = new URLSearchParams(window.location.search);
        const carteiras = urlParams.get('carteiras');
        let ids = carteiras?.split(',') || [];

        ids = ids.filter((value) => value !== id.toString());

        var baseUrl = "<?php echo $this->Url->build(['controller' => 'Portfolio', 'action' => 'comparacao']); ?>";

        if (ids.length) {

            window.location.href = baseUrl + '?carteiras=' + ids.join(',');
        } else {
            window.location.href = baseUrl;
        }
    }
</script>


<div class="Portfolios comparacao content">
    <?= $this->element('titleInfo', array('title' => __('Comparação de Carteiras'), 'h' => 1)) ?>

    <div class="column-responsive">
        <?= $this->element('titleInfo', array('title' => __('Selecione as carteiras para compararação'), 'h' => 2, 'info' => 'Os filtros são usados para restringir a busca por carteiras com características de seu interesse. Preencha um ou mais campos abaixo com as características desejadas e pressione o botão "Aplicar filtros" para refinar sua busca.')); ?>
        <form>
            <fieldset>
                <?= $this->Form->control('nome', ['type' => 'text', 'label' => __('Nome da carteira'), 'id' => 'nome-busca-carteiras']); ?>
                <div id="resultado-busca-carteiras"></div>
            </fieldset>
        </form>
    </div>

    <div class="row">
        <h3><?= __('Carteiras') ?></h3>
    </div>
    <div class="row">
        <div id="carteiras-selecionadas">
            <?php
            foreach ($carteirasInvestimentos as $carteira) {
            ?>
                <div> <?= $carteira['nome']; ?> <button onClick="removeCarteira(<?= $carteira['id']; ?>)">x</button></div>
            <?php
            }
            ?>
        </div>
    </div>

    <div class="row">
        <h3><?= __('Rentabilidade') ?></h3>
    </div>
    <div class="row">
        <div class="column-graph">
            <?php
            echo $this->element('titleInfo', array('title' => __('Rentabilidade'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
            $data = array();
            $data[] = "['Data'";
            foreach ($carteiras as $carteira) {
                $data[] = ",'" . $carteira . "'";
            }
            $data[] = "],";
            foreach ($rentabilidades as $date => $rentabilidade) {
                $data[] = "['" . $date . "'";
                foreach ($rentabilidade as $valor) {
                    $data[] = "," . $valor;
                }
                $data[] = "],";
            }

            echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'hAxisTitle' => '', 'chart' => 'chart1_div'));
            ?>
            <div id="chart1_div" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <div class="row">
        <h3><?= __('Volatilidade') ?></h3>
    </div>
    <div class="row">
        <div class="column-graph">
            <?php
            echo $this->element('titleInfo', array('title' => __('Volatilidade (Risco)'), 'align' => 'center', 'h' => 3, 'info' => __('...')));
            $data = array();
            $data[] = "['Data'";
            foreach ($carteiras as $carteira) {
                $data[] = ",'" . $carteira . "'";
            }
            $data[] = "],";
            foreach ($volatilidades as $date => $volatilidade) {
                $data[] = "['" . $date . "'";
                foreach ($volatilidade as $valor) {
                    $data[] = "," . $valor;
                }
                $data[] = "],";
            }

            echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => '', 'vAxisFormat' => 'percent', 'hAxisTitle' => '', 'chart' => 'chart2_div'));
            ?>
            <div id="chart2_div" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <div class="row">
        <h3><?= __('Retorno x Risco') ?></h3>
    </div>
    <div class="row">
        <div class="column-graph">
            <?php
            echo $this->element('titleInfo', array('title' => __('Retorno x Risco'), 'align' => 'center', 'h' => 2, 'info' => __('...')));
            $data = array();
            $data[] = "['ID', 'Risco', 'Retorno'],";
            foreach ($retornosRiscos as $retornoRisco) {
                $data[] = "['" . $retornoRisco['carteira'] . "'," . $retornoRisco['desvio_padrao'] . "," . $retornoRisco['rentabilidade'] . "],";
            }
            echo $this->element('googleChartFundo', array('data' => $data, 'title' => '', 'vAxisTitle' => 'Retorno', 'vAxisFormat' => 'percent', 'hAxisTitle' => 'Risco', 'hAxisFormat' => 'percent', 'chartType' => 'Bubble', 'chart' => 'chart3_div'));
            ?>
            <div id="chart3_div" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <div class="row">
        <h3><?= __('Índices Sharpe e Beta') ?></h3>
    </div>
    <div class="row">

    </div>

</div>
