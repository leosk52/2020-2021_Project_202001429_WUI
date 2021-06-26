<script>
    function selecionaCarteira(id) {
        const urlParams = new URLSearchParams(window.location.search);
        const carteiras = urlParams.get('carteiras');
        const ids = carteiras?.split(',') || [];

        if (!ids.includes(id.toString())) {
            ids.push(id);
        }

        var baseUrl = "<?php echo $this->Url->build(['controller' => 'Portfolio', 'action' => 'comparacao']); ?>";
        window.location.href = baseUrl + '?carteiras=' + ids.join(',');
    }
</script>

<div class="column-responsive column-80">
    <?php
    $qtd = count($carteiras_encontradas);
    if ($qtd == 0) {
        echo 'Nenhum carteira encontrada';
    } else if ($qtd == 1) {
        foreach ($carteiras_encontradas as $carteira) {
            echo '<div class="content">Carteira selecionada:</br><strong>' . $carteira['nome'] . ')</strong></div>';
        }
    } else {
        echo 'Carteiras encontradas (selecione a correta):';
    }
    ?>

    <?php if ($qtd > 1) { ?>
        <table>
            <?php foreach ($carteiras_encontradas as $carteira) { ?>
                <tr>
                    <td id="<?= $carteira['id'] ?>" onMouseOut="this.style.color = '#c0c0c0'" onMouseOver="this.style.cursor = 'pointer'; this.style.color = '#2a6496'" onclick="selecionaCarteira(<?= $carteira['id'] . ', \'' . $carteira['nome'] . '\'' ?>)">
                        <?= $carteira['nome'] ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>
