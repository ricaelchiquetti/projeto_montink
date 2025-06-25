# Projeto E-commerce Simples - Desafio Montink

Este é um projeto de um sistema de e-commerce simples, desenvolvido como parte de um desafio técnico. A aplicação foi construída com o framework PHP **CodeIgniter 3** e utiliza **Docker** para criar um ambiente de desenvolvimento padronizado e de fácil configuração.

## Funcionalidades Principais

-   **Gerenciamento de Produtos**: Crie, atualize e visualize produtos, incluindo controle de estoque por variações.
-   **Carrinho de Compras**: Adicione produtos ao carrinho, que é gerenciado via sessão.
-   **Cálculo de Frete Dinâmico**: O frete é calculado com base no subtotal do pedido.
-   **Sistema de Cupons**: Aplique cupons de desconto (percentual ou fixo) com regras de valor mínimo e data de validade.
-   **Consulta de CEP**: Integração com a API **ViaCEP** para preenchimento automático de endereço.
-   **Finalização de Pedido**: Salva os detalhes do pedido no banco de dados e atualiza o estoque de forma atômica (usando transações).
-   **Notificação por E-mail**: Envia um e-mail de confirmação ao cliente após a finalização do pedido.
-   **Webhook para Status de Pedidos**: Um endpoint seguro (`api/webhook/order_status`) recebe atualizações de status de sistemas externos, autenticado por um token.

## Tecnologias Utilizadas

-   **Backend**: PHP 7.4
-   **Framework**: CodeIgniter 3.1.11
-   **Banco de Dados**: MySQL 5.7
-   **Servidor Web**: Apache
-   **Containerização**: Docker e Docker Compose
-   **Frontend**: HTML, CSS, Bootstrap, JavaScript (jQuery)

---

## Como Iniciar e Rodar o Projeto

Siga os passos abaixo para configurar e rodar a aplicação em seu ambiente local.

### Pré-requisitos

-   [Docker](https://www.docker.com/get-started)
-   [Docker Compose](https://docs.docker.com/compose/install/)

### 1. Clonar o Repositório

Primeiro, clone este repositório para a sua máquina.

```bash
git clone <URL_DO_SEU_REPOSITORIO>
cd nome-da-pasta-do-projeto

2. Configurar o Ambiente
O arquivo docker-compose.yml na raiz do projeto já define os serviços necessários e as variáveis de ambiente.

a) Token do Webhook:
Abra o arquivo docker-compose.yml e altere a variável WEBHOOK_SECRET_TOKEN para um valor seguro de sua escolha.

# docker-compose.yml
services:
  app:
    # ...
    environment:
      - WEBHOOK_SECRET_TOKEN=meu-token-super-secreto-12345

b) Conexão com o Banco de Dados:
A configuração em src/application/config/database.php já está apontando para o serviço db do Docker, usando as credenciais definidas no docker-compose.yml. Nenhuma alteração é necessária aqui.

3. Construir e Subir os Contêineres
Com o Docker em execução, execute o seguinte comando na raiz do projeto:

docker-compose up -d --build

Este comando irá:

Construir a imagem da aplicação PHP com as extensões necessárias.

Iniciar os contêineres da aplicação (app) e do banco de dados (db) em segundo plano (-d).

4. Executar as Migrações do Banco
Após os contêineres estarem no ar, você precisa criar as tabelas no banco de dados.

Acesse o terminal do contêiner da aplicação:

docker-compose exec app bash

Dentro do contêiner, execute o seguinte comando para rodar as migrações:

php index.php migrate

Se tudo ocorrer bem, você verá a mensagem "Table Migrated Successfully." ou similar. Isso significa que as tabelas products, stock, orders e coupons foram criadas, e um cupom de exemplo foi inserido.

5. Acessar a Aplicação
Pronto! A aplicação está rodando. Abra seu navegador e acesse:

Loja: http://localhost:8080/

Painel de Gerenciamento: http://localhost:8080/management

Endpoint do Webhook
O endpoint para receber atualizações de status de pedidos está configurado e pronto para uso.

URL: http://localhost:8080/api/webhook/order_status

Método HTTP: POST

Header de Autenticação: X-Webhook-Token: <seu_token_configurado_no_docker_compose>

Exemplo de corpo da requisição (payload) para atualizar um status:

{
    "order_id": 1,
    "status": "enviado"
}

Exemplo para cancelar (remover) um pedido:

{
    "order_id": 1,
    "status": "cancelado"
}
