# Projeto E-commerce Simples - Desafio Montink

Sistema de e-commerce simples desenvolvido com **CodeIgniter 3**, utilizando **Docker** para ambiente padronizado.

---

## Funcionalidades Principais

- **Gerenciamento de Produtos**: Criação, atualização e visualização com controle de estoque por variações.
- **Carrinho de Compras**: Gerenciado via sessão, com adição e remoção de produtos.
- **Cálculo Dinâmico de Frete**: Baseado no subtotal do pedido.
- **Sistema de Cupons**: Suporta descontos percentuais e fixos, com regras de valor mínimo e validade.
- **Consulta de CEP**: Integração com API ViaCEP para preenchimento automático de endereço.
- **Finalização de Pedido**: Registro no banco e atualização atômica do estoque via transações.
- **Notificação por E-mail**: Confirmação automática ao cliente após pedido finalizado.
- **Webhook para Status de Pedidos**: Endpoint seguro para atualização externa do status.

---

## Tecnologias Utilizadas

- PHP 7.4
- CodeIgniter 3.1.11
- MySQL 5.7
- Apache
- Docker & Docker Compose
- HTML, CSS, Bootstrap, JavaScript (jQuery)

---

## Configuração e Execução

### Pré-requisitos

- Docker
- Docker Compose

### Passos para rodar

1. **Clonar o repositório**

```bash
git clone <URL_DO_REPOSITORIO>
cd nome-da-pasta-do-projeto
```

2. **Configurar ambiente**

- No `docker-compose.yml`, configure o token seguro para o webhook:

```yaml
services:
  app:
    environment:
      - WEBHOOK_SECRET_TOKEN=seu-token-super-secreto
```

- A configuração do banco em `src/application/config/database.php` já está pronta para usar o serviço Docker `db`.

3. **Construir e subir containers**

```bash
docker-compose up -d --build
```

4. **Rodar migrações**

Entre no container da aplicação:

```bash
docker-compose exec app bash
```

Execute as migrações para criar as tabelas:

```bash
php index.php migrate
```

5. **Acessar a aplicação**

- Loja: [http://localhost:8080/](http://localhost:8080/)
- Painel de gerenciamento: [http://localhost:8080/management](http://localhost:8080/management)

---

## Rotas Principais

### Frontend

| Rota                      | Método | Descrição                           |
|---------------------------|--------|-----------------------------------|
| `/` ou `/shop`             | GET    | Página principal da loja           |
| `/shop/place_order`        | POST   | Finalizar pedido                   |
| `/shop/get_address_by_cep/{cep}` | GET    | Consulta CEP via API ViaCEP       |

### Gerenciamento

| Rota                      | Método | Descrição                           |
|---------------------------|--------|-----------------------------------|
| `/management`              | GET    | Painel de gerenciamento            |
| `/management/save_product` | POST   | Salvar produto                     |

### Pedidos

| Rota           | Método | Descrição              |
|----------------|--------|------------------------|
| `/order`       | GET    | Listar/consultar pedidos|

### Cupons

| Rota               | Método | Descrição            |
|--------------------|--------|----------------------|
| `/coupons`          | GET    | Listar cupons         |
| `/coupons/save_coupon` | POST | Criar/atualizar cupom |

### API para Frontend (Ex: via Axios)

| Rota                      | Método | Descrição                       |
|---------------------------|--------|---------------------------------|
| `/api/cart/add/{product_id}` | POST   | Adicionar produto ao carrinho    |
| `/api/apply-coupon`         | POST   | Aplicar cupom ao carrinho        |

### Webhook para Atualização de Status de Pedidos

| Rota                           | Método | Descrição                         |
|--------------------------------|--------|---------------------------------|
| `/api/webhook/order_status`    | POST   | Atualiza status de pedido via webhook (autenticado por token) |

**Headers:**

- `X-Webhook-Token: <token configurado no docker-compose>`

**Exemplo de payload para atualizar status:**

```json
{
  "order_id": 1,
  "status": "enviado"
}
```

**Exemplo para cancelar um pedido:**

```json
{
  "order_id": 1,
  "status": "cancelado"
}
```

---

Se precisar de mais alguma coisa, é só pedir!
