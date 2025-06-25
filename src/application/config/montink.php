<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Configurações Personalizadas do Projeto
|--------------------------------------------------------------------------
|
| Este arquivo contém itens de configuração específicos da aplicação.
|
*/

/**
 * Token de segurança para o Webhook.
 *
 * A função getenv() lê a variável de ambiente 'WEBHOOK_SECRET_TOKEN'
 *
 * Se a variável não for encontrada, ele usa um valor padrão (fallback),
 * mas o ideal é que ela SEMPRE seja definida no ambiente.
 */
$config['webhook_secret_token'] = getenv('WEBHOOK_SECRET_TOKEN') ?: 'fallback_token_inseguro';

