<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_base_tables extends CI_Migration {

    public function up()
    {
        // Tabela de Produtos
        $this->dbforge->add_field(array(
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'created_at' => ['type' => 'DATETIME', 'null' => FALSE],
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('products');
        // Adiciona o valor padrão via query direta para máxima compatibilidade
        $this->db->query("ALTER TABLE `products` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");


        // Tabela de Estoque
        $this->dbforge->add_field(array(
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'product_id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE],
            'variation' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => TRUE],
            'quantity' => ['type' => 'INT', 'constraint' => 5],
            'created_at' => ['type' => 'DATETIME', 'null' => FALSE],
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('product_id');
        $this->dbforge->create_table('stock');
        $this->db->query("ALTER TABLE `stock` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");


        // Tabela de Cupons
        $this->dbforge->add_field(array(
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'code' => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => TRUE],
            'discount_type' => ['type' => "ENUM('percentage','fixed')", 'default' => 'fixed'],
            'value' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'min_purchase_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'valid_until' => ['type' => 'DATETIME'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => FALSE],
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('coupons');
        $this->db->query("ALTER TABLE `coupons` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Adiciona um cupom de exemplo
        $this->db->insert('coupons', [
            'code' => 'DESCONTO10',
            'discount_type' => 'percentage',
            'value' => 10.00,
            'min_purchase_amount' => 50.00,
            'valid_until' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'is_active' => 1
        ]);


        // Tabela de Pedidos
        $this->dbforge->add_field(array(
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'customer_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'customer_cep' => ['type' => 'VARCHAR', 'constraint' => 9],
            'customer_address' => ['type' => 'TEXT'],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'shipping_cost' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'total' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pending'],
            'coupon_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
            'created_at' => ['type' => 'DATETIME', 'null' => FALSE],
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('orders');
        $this->db->query("ALTER TABLE `orders` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->dbforge->drop_table('orders');
        $this->dbforge->drop_table('coupons');
        $this->dbforge->drop_table('stock');
        $this->dbforge->drop_table('products');
    }
}
