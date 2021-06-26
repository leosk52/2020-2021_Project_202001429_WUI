<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OperacoesFinanceira $operacoesFinanceira
 */


?>

<head>
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.css" />
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.js"></script>

    <style>
        .hidden {
            display: none;
        }
    </style>
</head>

<script>
    $(function() {
        let porValor;

        $('document').ready(function() {
            $("#data").datepicker({
                // format: 'yyyy-MM-dd',
                maxDate: 0,
                beforeShowDay: function(date) {
                    var dayOfWeek = date.getDay();
                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        return [false, ''];
                    }
                    return [true, ''];
                }
            });
            $.datepicker.regional['pt-BR'] = {
                closeText: 'Fechar',
                prevText: '&#x3c;Anterior',
                nextText: 'Pr&oacute;ximo&#x3e;',
                currentText: 'Hoje',
                monthNames: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho',
                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                    'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
                ],
                dayNames: ['Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sabado'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                weekHeader: 'Sm',
                dateFormat: 'dd/mm/yy',
                firstDay: 0,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ''
            };
            $.datepicker.setDefaults($.datepicker.regional['pt-BR']);

            $('#cnpj_fundo_id').selectize({
                valueField: "id",
                labelField: "DENOM_SOCIAL",
                searchField: "DENOM_SOCIAL",
                create: false,
                render: {
                    option: function(item, escape) {
                        return "<div>" + escape(item.CNPJ) + ' - ' + escape(item.DENOM_SOCIAL) + "</div>";
                    },
                },
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        url: "<?php echo $this->Url->build(['controller' => 'Fundos', 'action' => 'ajaxBuscaPorNome']); ?>",
                        type: "GET",
                        dataType: "json",
                        data: {
                            keyword: query,
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res) {
                            callback(res.fundos_encontrados);
                        },
                    });
                }
            });

            porValor = $('#por-valor').val();
            mostraPorValor();

            $('#por-valor').on('change', () => {
                porValor = $('#por-valor').val();
                mostraPorValor();
            });

            function mostraPorValor() {
                if (porValor === '0') {
                    $('#por-valor-campos').addClass('hidden');
                    $('#por-quantidade-campos').removeClass('hidden');
                    $('#valor-cota').prop("required", true);
                    $('#quantidade-cotas').prop("required", true);
                    $('#valor-total').prop("required", false);
                    $('#valor-total').val(0);
                } else if (porValor === '1') {
                    $('#por-quantidade-campos').addClass('hidden');
                    $('#por-valor-campos').removeClass('hidden');
                    $('#valor-total').prop("required", true);
                    $('#valor-cota').prop("required", false);
                    $('#quantidade-cotas').prop("required", false);
                    $('#valor-cota').val(0);
                    $('#quantidade-cotas').val(0);
                } else {
                    $('#por-valor-campos').addClass('hidden');
                    $('#por-quantidade-campos').addClass('hidden');
                    $('#valor-cota').prop("required", false);
                    $('#quantidade-cotas').prop("required", false);
                    $('#valor-total').prop("required", false);
                }
            }
        });


        $("#distribuidor_fundo_id").selectize({
            valueField: "id",
            labelField: "nome",
            searchField: "nome",
            create: false,
            render: {
                option: function(item, escape) {
                    return "<div>" + escape(item.nome) + "</div>";
                },
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    url: "<?php echo $this->Url->build(['controller' => 'DistribuidorFundos', 'action' => 'ajaxBuscaPorNome']); ?>",
                    type: "GET",
                    dataType: "json",
                    data: {
                        keyword: query,
                    },
                    error: function() {
                        callback();
                    },
                    success: function(res) {
                        callback(res.distribuidores_encontrados);
                    },
                });
            }
        });
    });
</script>


<div class="row">
    <!--<aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
	<?= $this->Html->link(__('List Operacoes Financeiras'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>-->
    <div class="column-responsive column-80">
        <div class="operacoesFinanceiras form content">
            <?= $this->element('titleInfo', array('title' => __('Nova Operação Financeira da Carteira "{0}"', $carteirasInvestimentos->nome), 'h' => 2)); ?>
            <?= $this->Form->create($operacoesFinanceira); ?>

            <fieldset>
                <!--<legend><?= __('Nova Operação Financeira da Carteira "{0}"', $carteirasInvestimentos->nome) ?></legend>-->
                <?php
                echo $this->Form->hidden('carteiras_investimento_id', ['value' => $carteirasInvestimentos->id]);
                echo $this->Form->control('cnpj_fundo_id', ['label' => __('Fundo de investimento'), 'id' => 'cnpj_fundo_id', 'empty' => true]);
                echo $this->Form->control('distribuidor_fundo_id', ['label' => __('Corretora ou Distribuidor do Fundo'), 'id' => 'distribuidor_fundo_id', 'empty' => true]);
                echo $this->Form->control('tipo_operacoes_financeira_id', ['label' => __('Tipo da Operação Financeira'), 'options' => $tipoOperacoesFinanceiras]);
                echo $this->Form->control('data', ['label' => __('Data'), 'type' => 'text', 'id' => 'data']);
                // echo $this->Form->control('data', ['label' => __('Data'), 'type' => 'date']);
                echo $this->Form->control('por_valor', ['label' => __('Adicionar por'), 'id' => 'por-valor', 'options' => [['value' => 1, 'text' => 'Por valor'], ['value' => 0, 'text' => 'Por quantidade e preço']], 'empty' => false]);
                ?>
                <div id="por-valor-campos" class="hidden">
                    <?php
                    echo $this->Form->control('valor_total', ['label' => __('Valor Total'), 'id' => 'valor-total', 'min' => 0]); //'before'=> 'R$'
                    ?>
                </div>
                <div id="por-quantidade-campos" class="hidden">
                    <?php
                    echo $this->Form->control('valor_cota', ['label' => __('Valor Unitário da Cota'), 'id' => 'valor-cota']);
                    echo $this->Form->control('quantidade_cotas', ['label' => __('Quantidade de Cotas'), 'id' => 'quantidade-cotas']);
                    ?>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Cadastrar')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
