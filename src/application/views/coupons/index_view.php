<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Cupons</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gerenciar Cupons</h2>
        <a href="<?php echo site_url('management'); ?>" class="btn btn-info">Gerenciar Produtos</a>
    </div>
    <hr>
    
    <!-- Mensagens de Feedback -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <div class="card p-3 mb-4">
        <h4>Adicionar Novo Cupom</h4>

        <?php echo form_open('coupons/save_coupon', ['class' => 'mt-3']); ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="code">Código</label>
                    <input type="text" class="form-control" name="code" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="value">Valor do Desconto</label>
                    <input type="number" step="0.01" class="form-control" name="value" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="discount_type">Tipo de Desconto</label>
                    <select name="discount_type" class="form-control">
                        <option value="fixed">Fixo (R$)</option>
                        <option value="percentage">Porcentagem (%)</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                 <div class="form-group col-md-4">
                    <label for="min_purchase_amount">Valor Mínimo da Compra</label>
                    <input type="number" step="0.01" class="form-control" name="min_purchase_amount">
                </div>
                <div class="form-group col-md-4">
                    <label for="valid_until">Válido até</label>
                    <input type="datetime-local" class="form-control" name="valid_until" required>
                </div>
                 <div class="form-group col-md-2 align-self-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Ativo</label>
                    </div>
                </div>
                <div class="form-group col-md-2 align-self-end">
                     <button type="submit" class="btn btn-primary btn-block">Salvar</button>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>


    <h4 class="mt-5">Cupons Existentes</h4>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Código</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Mínimo Compra</th>
                <th>Validade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($coupons)): foreach ($coupons as $coupon): ?>
            <tr>
                <td><?php echo $coupon->code; ?></td>
                <td><?php echo $coupon->discount_type; ?></td>
                <td><?php echo $coupon->value; ?></td>
                <td>R$ <?php echo number_format($coupon->min_purchase_amount, 2, ',', '.'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($coupon->valid_until)); ?></td>
                <td><?php echo $coupon->is_active ? 'Ativo' : 'Inativo'; ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
                <td colspan="6" class="text-center">Nenhum cupom encontrado.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
