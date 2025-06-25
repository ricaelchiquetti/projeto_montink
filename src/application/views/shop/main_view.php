<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Loja Simples</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Axios primeiro -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Definição do componente Alpine.js -->
    <script>
    function shop(initialCart) {
        return {
            cart: initialCart,
            productLoading: null,
            loading: false,
            couponCode: '',
            couponMessage: '',
            couponStatus: null,
            feedback: { message: '', type: '' },
            cep: '',
            loadingCep: false,
            address: { full: '', bairro: '' },

            formatCurrency(value) {
                return parseFloat(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            showFeedback(message, type) {
                this.feedback = { message, type };
                setTimeout(() => { this.feedback = { message: '', type: '' }; }, 3000);
            },

            async addToCart(productId) {
                this.productLoading = productId;
                const formData = new FormData();
                formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
                try {
                    const response = await axios.post(`<?php echo site_url('api/cart/add/'); ?>/${productId}`, formData);
                    this.cart = response.data.cart;
                    this.showFeedback(response.data.message, 'success');
                } catch (error) {
                    this.showFeedback(error.response?.data?.message || 'Erro ao adicionar produto.', 'error');
                } finally {
                    this.productLoading = null;
                }
            },

            async applyCoupon() {
                if (!this.couponCode) {
                    this.couponMessage = 'Por favor, insira um código.';
                    this.couponStatus = false;
                    return;
                }
                this.loading = true;
                this.couponMessage = '';
                const formData = new FormData();
                formData.append('coupon_code', this.couponCode);
                formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

                try {
                    const response = await axios.post('<?php echo site_url("api/apply-coupon"); ?>', formData);
                    this.cart = response.data.cart;
                    this.couponMessage = response.data.message;
                    this.couponStatus = true;
                } catch (error) {
                    this.couponMessage = error.response?.data?.message || 'Erro ao aplicar cupom.';
                    this.couponStatus = false;
                } finally {
                    this.loading = false;
                }
            },

            async getAddressByCep() {
                const cleanCep = this.cep.replace(/\D/g, '');
                if (cleanCep.length !== 8) return;
                this.loadingCep = true;
                try {
                    const response = await fetch(`<?php echo site_url('shop/get_address_by_cep/'); ?>/${cleanCep}`);
                    const data = await response.json();
                    if (data && !data.erro) {
                        this.address.full = `${data.logradouro}, ${data.localidade} - ${data.uf}`;
                        this.address.bairro = data.bairro;
                    } else {
                        this.showFeedback('CEP não encontrado.', 'error');
                        this.address = { full: '', bairro: '' };
                    }
                } catch (error) {
                    console.error('Erro ao buscar CEP:', error);
                    this.showFeedback('Não foi possível buscar o endereço.', 'error');
                } finally {
                    this.loadingCep = false;
                }
            }
        }
    }
    </script>

    <!-- Alpine.js por último -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body>

<div class="container mt-5"
     x-data="shop(<?php echo htmlspecialchars(json_encode($cart ?? ['items' => [], 'subtotal' => 0, 'discount' => 0, 'shipping_cost' => 0, 'total' => 0]), ENT_QUOTES, 'UTF-8'); ?>)">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Nossos Produtos</h1>
        <a href="<?php echo site_url('management'); ?>" class="btn btn-secondary">Área de Gerenciamento</a>
    </div>

    <template x-if="feedback.message">
        <div :class="`alert ${feedback.type === 'success' ? 'alert-success' : 'alert-danger'}`" x-text="feedback.message"></div>
    </template>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
    <?php endif; ?>

    <div class="row">
        <?php if (!empty($products)): foreach($products as $product): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product->name; ?></h5>
                        <p class="card-text">Variação: <?php echo $product->variation; ?></p>
                        <p class="card-text"><strong>Preço: R$ <?php echo number_format($product->price, 2, ',', '.'); ?></strong></p>
                        <p class="card-text">Estoque: <?php echo $product->quantity > 0 ? $product->quantity : 'Indisponível'; ?></p>

                        <button class="btn btn-success"
                                @click="addToCart(<?php echo $product->id; ?>)"
                                :disabled="productLoading === <?php echo $product->id; ?> || <?php echo $product->quantity <= 0 ? 'true' : 'false'; ?>">
                            <span x-show="productLoading !== <?php echo $product->id; ?>"><?php echo $product->quantity > 0 ? 'Comprar' : 'Indisponível'; ?></span>
                            <span x-show="productLoading === <?php echo $product->id; ?>">Adicionando...</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <p class="col-12">Nenhum produto cadastrado no momento.</p>
        <?php endif; ?>
    </div>

    <hr>

    <h2>Carrinho de Compras</h2>

    <template x-if="cart && cart.items && Object.keys(cart.items).length > 0">
        <div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Qtd.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in Object.values(cart.items)" :key="item.id">
                        <tr>
                            <td x-text="`${item.name} (${item.variation})`"></td>
                            <td x-text="`R$ ${formatCurrency(item.price)}`"></td>
                            <td x-text="item.qty"></td>
                            <td x-text="`R$ ${formatCurrency(item.subtotal)}`"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="text-right">
                <p><strong>Subtotal:</strong> R$ <span x-text="formatCurrency(cart.subtotal)"></span></p>
                <template x-if="cart.discount > 0">
                    <p class="text-success">
                        <strong>Desconto (<span x-text="cart.coupon_code"></span>):</strong> 
                        - R$ <span x-text="formatCurrency(cart.discount)"></span>
                    </p>
                </template>
                <p><strong>Frete:</strong> R$ <span x-text="formatCurrency(cart.shipping_cost)"></span></p>
                <h4><strong>Total:</strong> R$ <span x-text="formatCurrency(cart.total)"></span></h4>
            </div>

            <form @submit.prevent="applyCoupon" class="form-inline my-4">
                <input type="text" class="form-control mb-2 mr-sm-2" x-model="couponCode" placeholder="Cupom de desconto" :disabled="loading">
                <button type="submit" class="btn btn-secondary mb-2" :disabled="loading">
                    <span x-show="!loading">Aplicar</span>
                    <span x-show="loading">Aplicando...</span>
                </button>
                <span x-text="couponMessage" class="ml-2" :class="{'text-success': couponStatus, 'text-danger': !couponStatus && couponMessage}"></span>
            </form>

            <h4>Finalizar Compra</h4>
            <?php echo form_open('shop/place_order'); ?>
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="customer_email" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>CEP</label>
                        <input type="text" name="customer_cep" class="form-control" x-model="cep" @blur="getAddressByCep" required>
                    </div>
                    <div class="form-group col-md-7">
                        <label>Endereço</label>
                        <input type="text" name="customer_address" class="form-control" x-model="address.full" readonly>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Bairro</label>
                        <input type="text" class="form-control" x-model="address.bairro" readonly>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Finalizar Pedido</button>
            <?php echo form_close(); ?>
        </div>
    </template>

    <template x-if="!cart || !cart.items || Object.keys(cart.items).length === 0">
        <p>Seu carrinho está vazio.</p>
    </template>
</div>

</body>
</html>
