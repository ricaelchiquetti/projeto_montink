<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini ERP - Loja e Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; }
        .sticky-top { top: 1rem; }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="text-center mb-4">
            <h1>Mini ERP</h1>
            <p class="lead">Gestão de Produtos e Vendas</p>
        </div>
        <div id="alert-placeholder"></div>

        <div class="row g-5">
            <div class="col-lg-5">
                <h4>Gestão de Produtos</h4>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i> Cadastrar / Editar Produto
                    </div>
                    <div class="card-body">
                        <form id="form-product">
                            <input type="hidden" id="product_id">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Preço (R$)</label>
                                    <input type="number" class="form-control" id="price" step="0.01" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="inventory" class="form-label">Estoque</label>
                                    <input type="number" class="form-control" id="inventory" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar</button>
                                <button type="button" class="btn btn-secondary" onclick="clearForm()">Limpar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <h4>Loja</h4>
                <div id="product-list" class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                    </div>
                
                <h4 class="mt-5">Carrinho de Compras</h4>
                <div class="card sticky-top">
                    <div class="card-body">
                        <div id="cart-items">
                            <p class="text-muted">Seu carrinho está vazio.</p>
                        </div>
                        <hr>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between"><span>Subtotal:</span> <strong id="cart-subtotal">R$ 0,00</strong></li>
                            <li class="list-group-item d-flex justify-content-between"><span>Frete:</span> <strong id="cart-shipping">R$ 0,00</strong></li>
                            <li class="list-group-item d-flex justify-content-between bg-light"><h5>Total:</h5> <h5 id="cart-total">R$ 0,00</h5></li>
                        </ul>
                        <hr>
                        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal" id="btn-checkout" disabled>
                            <i class="fas fa-check-circle me-2"></i>Finalizar Compra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Finalizar Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="form-checkout">
                        <h6>Dados de Entrega</h6>
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nome Completo</label>
                            <input type="text" id="customer_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">E-mail</label>
                            <input type="email" id="customer_email" class="form-control" required>
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="customer_zipcode" placeholder="CEP" required>
                            <button class="btn btn-outline-secondary" type="button" id="btn-viacep">Buscar Endereço</button>
                        </div>
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Endereço</label>
                            <input type="text" id="customer_address" class="form-control" placeholder="Rua, Nº, Bairro, Cidade - UF" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Confirmar e Pagar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '<?php echo base_url(); ?>';
        // CORREÇÃO: Removido o 'index.php' da URL da API
        const API_URL = `${BASE_URL}shop/`;

        // --- FUNÇÕES DE UTILIDADE ---
        const formatCurrency = val => val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
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

        // --- LÓGICA DE PRODUTOS ---
        const clearForm = () => {
            document.getElementById('form-product').reset();
            document.getElementById('product_id').value = '';
        }

        const loadProducts = async () => {
            try {
                const products = await apiCall('api_list_products');
                const listEl = document.getElementById('product-list');
                listEl.innerHTML = '';
                if (!products.length) {
                    listEl.innerHTML = '<p class="text-muted col-12">Nenhum produto cadastrado.</p>';
                    return;
                }
                products.forEach(p => {
                    const disabled = p.inventory <= 0 ? 'disabled' : '';
                    const stockLabel = p.inventory <= 0 ? 'Sem estoque' : `${p.inventory} em estoque`;
                    const card = `
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title">${p.name}</h5>
                                        <div>
                                            <a href="#" onclick="editProduct(event, ${p.id})"><i class="fas fa-edit text-info"></i></a>
                                            <a href="#" onclick="deleteProduct(event, ${p.id})"><i class="fas fa-trash text-danger ms-2"></i></a>
                                        </div>
                                    </div>
                                    <p class="card-text fs-5 text-success fw-bold">${formatCurrency(parseFloat(p.price))}</p>
                                    <small class="text-muted">${stockLabel}</small>
                                </div>
                                <div class="card-footer bg-white border-0 pb-3">
                                    <button class="btn btn-sm btn-outline-primary w-100" onclick="addToCart(${p.id})" ${disabled}>
                                        <i class="fas fa-cart-plus me-2"></i> Adicionar ao Carrinho
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    listEl.innerHTML += card;
                });
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        document.getElementById('form-product').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                id: document.getElementById('product_id').value,
                name: document.getElementById('name').value,
                price: document.getElementById('price').value,
                inventory: document.getElementById('inventory').value,
            };
            try {
                const result = await apiCall('api_save_product', 'POST', data);
                showAlert(result.message);
                clearForm();
                loadProducts();
                updateCart();
            } catch (error) { showAlert(error.message, 'danger'); }
        });
        
        const editProduct = async (e, id) => {
            e.preventDefault();
            const products = await apiCall('api_list_products');
            const product = products.find(p => p.id == id);
            if(product) {
                document.getElementById('product_id').value = product.id;
                document.getElementById('name').value = product.name;
                document.getElementById('price').value = product.price;
                document.getElementById('inventory').value = product.inventory;
                window.scrollTo(0,0);
            }
        };

        const deleteProduct = async (e, id) => {
            e.preventDefault();
            if (!confirm('Tem certeza que deseja deletar este produto?')) return;
            try {
                const result = await apiCall('api_delete_product', 'POST', { id });
                showAlert(result.message);
                loadProducts();
                updateCart();
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        // --- LÓGICA DO CARRINHO ---
        const updateCart = async () => {
             try {
                const cart = await apiCall('api_get_cart');
                const itemsEl = document.getElementById('cart-items');
                itemsEl.innerHTML = '';

                if(cart.items.length) {
                    cart.items.forEach(item => {
                        itemsEl.innerHTML += `<p class="mb-1">${item.quantity}x ${item.name} - <strong>${formatCurrency(item.price * item.quantity)}</strong></p>`;
                    });
                    document.getElementById('btn-checkout').disabled = false;
                } else {
                    itemsEl.innerHTML = '<p class="text-muted">Seu carrinho está vazio.</p>';
                    document.getElementById('btn-checkout').disabled = true;
                }

                document.getElementById('cart-subtotal').textContent = formatCurrency(cart.subtotal);
                document.getElementById('cart-shipping').textContent = formatCurrency(cart.shipping_cost);
                document.getElementById('cart-total').textContent = formatCurrency(cart.total);
             } catch (error) { showAlert(error.message, 'danger'); }
        };

        const addToCart = async (id) => {
            try {
                await apiCall('api_add_to_cart', 'POST', { product_id: id });
                updateCart();
            } catch (error) { showAlert(error.message, 'danger'); }
        };

        // --- LÓGICA DE CHECKOUT ---
        document.getElementById('btn-viacep').addEventListener('click', async function() {
            const cep = document.getElementById('customer_zipcode').value.replace(/\D/g, '');
            if(cep.length !== 8) return;
            this.disabled = true; this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                if(data.erro) throw new Error();
                document.getElementById('customer_address').value = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
            } catch (error) {
                showAlert('CEP não encontrado.', 'warning');
            } finally {
                this.disabled = false; this.innerHTML = 'Buscar Endereço';
            }
        });

        document.getElementById('form-checkout').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                name: document.getElementById('customer_name').value,
                email: document.getElementById('customer_email').value,
                zipcode: document.getElementById('customer_zipcode').value,
                address: document.getElementById('customer_address').value,
            };
            try {
                const result = await apiCall('api_checkout', 'POST', data);
                showAlert(result.message);
                bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
                loadProducts();
                updateCart();
            } catch (error) { showAlert(error.message, 'danger'); }
        });
        
        // --- INICIALIZAÇÃO ---
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
            updateCart();
        });
    </script>
</body>
</html>
