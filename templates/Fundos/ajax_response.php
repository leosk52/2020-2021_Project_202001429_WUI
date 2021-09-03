<script>
	function sendXHR(id) {
		var data = id;
		$.ajax({
			method: 'get',
			url: "<?php echo $this->Url->build(['controller' => 'Fundos', 'action' => 'Ajaxsearch']); ?>",
			data: {id: data},

			success: function (response) {
				$('.cnpj_fundo_id').html(data)
				$('.resultado_busca').html(response),
				$('.resultado_id').val(data);
			}
		});
	}

</script>

<div class="column-responsive column-80">
	<?php
	$qtd = count($fundos_encontrados);
	if ($qtd == 0) {
		echo 'Nenhum fundo encontrado';
	} else if ($qtd == 1) {
		foreach ($fundos_encontrados as $fundo) {
			//echo 'Fundo selecionado: <strong>' . $fundo['DENOM_SOCIAL'] . ' (' . $fundo['CNPJ'] . ')</strong>';
			echo '<div class="content">Fundo selecionado:</br><strong>' . $fundo['DENOM_SOCIAL'] . ' (' . $fundo['CNPJ'] . ')</strong></div>';
		}
	} else {
		echo 'Fundos encontrados (selecione o correto):';
	}
	?>

	<?php if ($qtd > 1) { ?>
		<table>
			<!-- TODO: Fix color styles -->
			<?php foreach ($fundos_encontrados as $fundo) { ?>
				<tr>
					<td id="<?= $fundo['id'] ?>" onMouseOut="this.style.color = '#c0c0c0'" onMouseOver="this.style.cursor = 'pointer'; this.style.color = '#2a6496'" onclick="sendXHR(<?= $fundo['id'] ?>)">  
						<?= $fundo['DENOM_SOCIAL'] . ' (' . $fundo['CNPJ'] . ')' ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
</div>