<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Pedidos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gerenciamento de Pedidos</h2>
        <div>
            <a href="<?= site_url('coupons'); ?>" class="btn btn-info">Gerenciar Cupons</a>
            <a href="<?= site_url('management'); ?>" class="btn btn-info">Gerenciar Produtos</a>
        </div>
    </div>
    <hr>

    <?php if (empty($orders)): ?>
        <div class="alert alert-secondary text-center">Nenhum pedido encontrado.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>CEP</th>
                    <th>Subtotal</th>
                    <th>Frete</th>
                    <th>Desconto</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order->id ?></td>
                        <td><?= html_escape($order->customer_name) ?></td>
                        <td><?= html_escape($order->customer_email) ?></td>
                        <td><?= html_escape($order->customer_cep) ?></td>
                        <td>R$ <?= number_format($order->subtotal, 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($order->shipping_cost, 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($order->discount_amount, 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($order->total, 2, ',', '.') ?></td>
                        <td>
                            <?php
                                $status_class = 'badge-secondary';
                                switch ($order->status) {
                                    case 'pending': $status_class = 'badge-warning'; break;
                                    case 'completed': $status_class = 'badge-success'; break;
                                    case 'cancelled': $status_class = 'badge-danger'; break;
                                }
                            ?>
                            <span class="badge <?= $status_class ?>"><?= ucfirst(html_escape($order->status)) ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($order->created_at)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>