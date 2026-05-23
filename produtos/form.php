<form id="produtoForm" method="POST" action="<?php echo $form_action ?? ''; ?>">
    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label for="nome">Nome do Produto *</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $produto['nome'] ?? ''; ?>" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="preco">Preço de Venda (R$) *</label>
                <input type="text" class="form-control" id="preco" name="preco" data-mask="currency" value="<?php echo isset($produto['preco']) ? number_format($produto['preco'], 2, ',', '.') : ''; ?>" required>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo $produto['descricao'] ?? ''; ?></textarea>
            </div>
        </div>
    </div>
</form>