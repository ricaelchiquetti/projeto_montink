<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gerenciamento - Mini ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Painel de Gerenciamento</h1>
                <p class="lead">Gestão de Pedidos e Cupons</p>
            </div>
            <a href="<?php echo base_url('shop'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-store me-2"></i>Ir para a Loja
            </a>
        </div>
        <div id="alert-placeholder"></div>

        <!-- Abas de Navegação -->
        <ul class="nav nav-tabs" id="managementTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-tab-pane" type="button" role="tab">Gestão de Pedidos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="coupons-tab" data-bs-toggle="tab" data-bs-target="#coupons-tab-pane" type="button" role="tab">Gestão de Cupons</button>
            </li>
        </ul>

        <!-- Conteúdo das Abas -->
        <div class="tab-content" id="managementTabsContent">
            <!-- Aba de Pedidos -->
            <div class="tab-pane fade show active" id="orders-tab-pane" role="tabpanel">
                <div class="card card-body border-top-0">
                    <h5 class="card-title">Pedidos Recebidos</h5>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="orders-list"></tbody>
                    </table>
                </div>
            </div>

            <!-- Aba de Cupons -->
            <div class="tab-pane fade" id="coupons-tab-pane" role="tabpanel">
                <div class="card card-body border-top-0">
                    <div class="row">
                        <div class="col-md-4">
                             <h5 class="card-title mb-3">Cadastrar / Editar Cupom</h5>
                             <form id="form-coupon">
                                <input type="hidden" id="coupon_id">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Código do Cupom</label>
                                    <input type="text" id="code" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="discount_type" class="form-label">Tipo de Desconto</label>
                                    <select id="discount_type" class="form-select">
                                        <option value="fixed">Fixo (R$)</option>
                                        <option value="percentage">Percentual (%)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="discount_value" class="form-label">Valor do Desconto</label>
                                    <input type="number" id="discount_value" step="0.01" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="min_order_value" class="form-label">Valor Mínimo do Pedido (R$)</label>
                                    <input type="number" id="min_order_value" step="0.01" class="form-control" required>
                                </div>
                                 <div class="mb-3">
                                    <label for="expiration_date" class="form-label">Data de Validade</label>
                                    <input type="date" id="expiration_date" class="form-control" required>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">Ativo</label>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Salvar Cupom</button>
                                    <button type="button" class="btn btn-secondary" onclick="clearCouponForm()">Limpar</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-8">
                             <h5 class="card-title mb-3">Cupons Cadastrados</h5>
                             <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Valor</th>
                                        <th>Validade</th>
                                        <th>Ativo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="coupons-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = '<?php echo base_url("management/"); ?>';

        // --- FUNÇÕES DE UTILIDADE ---
        const formatCurrency = val => parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const formatDate = dateStr => new Date(dateStr).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
        const showAlert = (message, type = 'success') => {
            const el = document.getElementById('alert-placeholder');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `<div class="alert alert-${type} alert-dismissible" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
            el.append(wrapper);
            setTimeout(() => wrapper.remove(), 5000);
        };
        const apiCall = async (endpoint, method = 'GET', body = null) => {
            const options = { method, headers: { 'Content-Type': 'application/json' } };
            if (body) options.body = JSON.stringify(body);
            const response = await fetch(API_URL + endpoint, options);
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Ocorreu um erro na requisição.');
            }
            return response.json();
        };

        // --- LÓGICA DE PEDIDOS ---
        const loadOrders = async () => {
            try {
                const orders = await apiCall('api_list_orders');
                const listEl = document.getElementById('orders-list');
                listEl.innerHTML = '';
                if(!orders.length) {
                    listEl.innerHTML = '<tr><td colspan="7" class="text-center">Nenhum pedido encontrado.</td></tr>';
                    return;
                }
                orders.forEach(order => {
                    const statusOptions = ['pendente', 'pago', 'enviado', 'cancelado', 'finalizado']
                        .map(s => `<option value="${s}" ${s === order.status ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`).join('');

                    listEl.innerHTML += `
                        <tr>
                            <td>#${order.id}</td>
                            <td>${order.customer_name}</td>
                            <td>${order.customer_email}</td>
                            <td>${formatCurrency(order.total_amount)}</td>
                            <td>
                                <select class="form-select form-select-sm" onchange="updateOrderStatus(${order.id}, this.value)">
                                    ${statusOptions}
                                </select>
                            </td>
                            <td>${formatDate(order.created_at)}</td>
                            <td><button class="btn btn-sm btn-outline-danger" onclick="deleteOrder(${order.id})"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    `;
                });
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        const updateOrderStatus = async (id, status) => {
            try {
                const result = await apiCall('api_update_order_status', 'POST', { id, status });
                showAlert(result.message);
                loadOrders(); // Recarrega para refletir a mudança
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        const deleteOrder = async (id) => {
            if(!confirm(`Tem certeza que deseja deletar o pedido #${id}? Esta ação não pode ser desfeita.`)) return;
            try {
                const result = await apiCall('api_delete_order', 'POST', { id });
                showAlert(result.message);
                loadOrders();
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        // --- LÓGICA DE CUPONS ---
        const clearCouponForm = () => {
            document.getElementById('form-coupon').reset();
            document.getElementById('coupon_id').value = '';
        };

        const loadCoupons = async () => {
            try {
                const coupons = await apiCall('api_list_coupons');
                const listEl = document.getElementById('coupons-list');
                listEl.innerHTML = '';
                if(!coupons.length) {
                    listEl.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum cupom encontrado.</td></tr>';
                    return;
                }
                coupons.forEach(coupon => {
                    const activeBadge = coupon.is_active == 1 ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-danger">Não</span>';
                    const discount = coupon.discount_type === 'fixed' ? formatCurrency(coupon.discount_value) : `${coupon.discount_value}%`;
                    listEl.innerHTML += `
                        <tr>
                            <td>${coupon.code}</td>
                            <td>${discount}</td>
                            <td>${formatDate(coupon.expiration_date)}</td>
                            <td>${activeBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="editCoupon(${coupon.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteCoupon(${coupon.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        document.getElementById('form-coupon').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                id: document.getElementById('coupon_id').value,
                code: document.getElementById('code').value,
                discount_type: document.getElementById('discount_type').value,
                discount_value: document.getElementById('discount_value').value,
                min_order_value: document.getElementById('min_order_value').value,
                expiration_date: document.getElementById('expiration_date').value,
                is_active: document.getElementById('is_active').checked ? 1 : 0
            };
            try {
                const result = await apiCall('api_save_coupon', 'POST', data);
                showAlert(result.message);
                clearCouponForm();
                loadCoupons();
            } catch (error) { showAlert(error.message, 'danger'); }
        });

        const editCoupon = async (id) => {
            try {
                const coupon = await apiCall(`api_get_coupon/${id}`);
                document.getElementById('coupon_id').value = coupon.id;
                document.getElementById('code').value = coupon.code;
                document.getElementById('discount_type').value = coupon.discount_type;
                document.getElementById('discount_value').value = coupon.discount_value;
                document.getElementById('min_order_value').value = coupon.min_order_value;
                document.getElementById('expiration_date').value = coupon.expiration_date;
                document.getElementById('is_active').checked = coupon.is_active == 1;
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        const deleteCoupon = async (id) => {
            if(!confirm(`Tem certeza que deseja deletar este cupom?`)) return;
            try {
                const result = await apiCall('api_delete_coupon', 'POST', { id });
                showAlert(result.message);
                loadCoupons();
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        // --- INICIALIZAÇÃO ---
        document.addEventListener('DOMContentLoaded', () => {
            loadOrders();
            loadCoupons();
        });
    </script>
</body>
</html>
