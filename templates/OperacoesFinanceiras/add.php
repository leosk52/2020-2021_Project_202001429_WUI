<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OperacoesFinanceira $operacoesFinanceira
 */

use App\Model\Entity\CnpjFundo;

?>

<script>
	$('document').ready(function () {
		$('#nome-ou-cnpj-fundo-busca').keyup(function () {
			var searchkey = $(this).val();
            searchFundos(searchkey);            
		});

		function searchFundos(keyword) {
			var data = keyword;
			$.ajax({
				method: 'get',
				url: "<?php echo $this->Url->build(['controller' => 'Fundos', 'action' => 'Ajaxsearch']); ?>",
				data: {keyword: data},

				success: function (response) {
					$('.resultado_busca').html(response);
				}
			});
		};
	});
</script>

<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Operacoes Financeiras'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="operacoesFinanceiras form content">
            <?= $this->Form->create($operacoesFinanceira) ?>
            <fieldset>
                <?php               
                echo $this->Form->control('nome_ou_cnpj_fundo_busca', ['label' => __('Nome do fundo para busca'), 'type' => 'text']);
                ?>
                
                <div class="resultado_busca"></div>
                <div>
                    <h4 class="heading"> Insira o ID que será retornado no campo Cnpj Fundo</h4>
                </div>
                <div class="cnpj_fundo_id"></div>

                <legend><?= __('Nova Operação Financeira') ?></legend>
                <?php

                    echo $this->Form->control('carteiras_investimento_id', ['options' => $carteirasInvestimentos]);
                    echo $this->Form->control('cnpj_fundo_id', ['type' => 'text']);
                    echo $this->Form->control('distribuidor_fundo_id', ['options' => $distribuidorFundos, 'empty' => true]);
                    echo $this->Form->control('tipo_operacoes_financeira_id', ['options' => $tipoOperacoesFinanceiras]);
                    echo $this->Form->control('por_valor');
                    echo $this->Form->control('valor_total');
                    echo $this->Form->control('valor_cota');
                    echo $this->Form->control('quantidade_cotas');
                    echo $this->Form->control('data');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'))?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
