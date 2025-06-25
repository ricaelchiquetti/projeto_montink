<!-- ARQUIVO 1: produtos.php (padronizado) -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Produtos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container mt-5" x-data="productForm()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gerenciar Produtos</h2>
        <div>
            <a href="<?php echo site_url('coupons'); ?>" class="btn btn-info">Gerenciar Cupons</a>
            <a href="<?php echo site_url('orders'); ?>" class="btn btn-info">Gerenciar Pedidos</a>
        </div>
    </div>
    <hr>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <div class="card p-3 mb-4">
        <div class="card-header-flex">
            <h4 x-text="formTitle">Adicionar Novo Produto</h4>
            <button type="button" class="btn btn-secondary btn-sm" @click="clearForm()">Limpar Formulário</button>
        </div>

        <?php echo form_open('management/save_product'); ?>
        <input type="hidden" name="product_id" x-model="product.id">
        <input type="hidden" name="stock_id" x-model="product.stock_id">

        <div class="form-row mt-3">
            <div class="form-group col-md-6">
                <label for="name">Nome do Produto</label>
                <input type="text" class="form-control" name="name" x-model="product.name" required>
            </div>
            <div class="form-group col-md-3">
                <label for="price">Preço (R$)</label>
                <input type="text" class="form-control" name="price" x-model="product.price" required placeholder="ex: 19,99">
            </div>
            <div class="form-group col-md-3">
                <label for="variation">Variação</label>
                <input type="text" class="form-control" name="variation" x-model="product.variation">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="quantity">Estoque</label>
                <input type="number" class="form-control" name="quantity" x-model="product.quantity" required>
            </div>
            <div class="form-group col-md-9 align-self-end">
                <button type="submit" class="btn btn-primary">Salvar Produto</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>

    <h4 class="mt-5">Produtos Existentes</h4>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Nome</th>
                <th>Preço</th>
                <th>Variação</th>
                <th>Estoque</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products ?? [] as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>R$ <?php echo number_format($p->price, 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($p->variation ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $p->quantity ?? '0'; ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" @click="editProduct({
                            id: '<?php echo $p->id; ?>',
                            stock_id: '<?php echo $p->stock_id ?? ''; ?>',
                            name: '<?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?>',
                            price: '<?php echo number_format($p->price, 2, ',', '.'); ?>',
                            variation: '<?php echo htmlspecialchars($p->variation ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                            quantity: '<?php echo $p->quantity ?? '0'; ?>'
                        })">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhum produto cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function productForm() {
    return {
        formTitle: 'Adicionar Novo Produto',
        product: { id: '', stock_id: '', name: '', price: '', variation: '', quantity: '' },
        editProduct(productData) {
            this.formTitle = 'Editar Produto';
            this.product = productData;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        clearForm() {
            this.formTitle = 'Adicionar Novo Produto';
            this.product = { id: '', stock_id: '', name: '', price: '', variation: '', quantity: '' };
        }
    }
}
</script>
</body>
</html>

