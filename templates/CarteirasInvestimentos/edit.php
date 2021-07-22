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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $carteirasInvestimento->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $carteirasInvestimento->nome), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Carteiras Investimentos'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>-->
    <div class="column-responsive column-80">
        <div class="carteirasInvestimentos form content">
            <?= $this->Form->create($carteirasInvestimento) ?>
            <fieldset>
                <legend><?= __('Edit Carteiras Investimento') ?></legend>
                <?php
                    //echo $this->Form->control('usuario_id', ['options' => $usuarios]);
                    echo $this->Form->control('nome');
                    echo $this->Form->control('descricao');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
