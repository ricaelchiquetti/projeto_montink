<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_base_tables extends CI_Migration {

    public function up()
    {
        // Usando a biblioteca DB Forge para criar as tabelas
        $this->load->dbforge();

        // Tabela `products`
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'price' => array('type' => 'DECIMAL', 'constraint' => '10,2'),
            'created_at' => array('type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'),
            'updated_at' => array('type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'on update' => 'CURRENT_TIMESTAMP'),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('products');

        // Tabela `product_variations`
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'product_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'variation_name' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'variation_value' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'created_at' => array('type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('product_variations');
        $this->db->query('ALTER TABLE `product_variations` ADD FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE');
        
        // Tabela `inventory`
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'product_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'variation_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'quantity' => array('type' => 'INT', 'constraint' => '11', 'default' => 0),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('inventory');
        $this->db->query('ALTER TABLE `inventory` ADD FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE');
        $this->db->query('ALTER TABLE `inventory` ADD FOREIGN KEY (`variation_id`) REFERENCES `product_variations`(`id`) ON DELETE CASCADE');
        
        // Tabela `coupons`
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'code' => array('type' => 'VARCHAR', 'constraint' => '50', 'unique' => TRUE),
            'discount_type' => array('type' => 'ENUM("percentage","fixed")', 'default' => 'fixed'),
            'discount_value' => array('type' => 'DECIMAL', 'constraint' => '10,2'),
            'min_order_value' => array('type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'),
            'expiration_date' => array('type' => 'DATE'),
            'is_active' => array('type' => 'TINYINT', 'constraint' => '1', 'default' => 1),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('coupons');

        // Tabela `orders`
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'customer_name' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'customer_email' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'customer_zipcode' => array('type' => 'VARCHAR', 'constraint' => '9'),
            'customer_address' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'subtotal' => array('type' => 'DECIMAL', 'constraint' => '10,2'),
            'shipping_cost' => array('type' => 'DECIMAL', 'constraint' => '10,2'),
            'discount_amount' => array('type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'),
            'total_amount' => array('type' => 'DECIMAL', 'constraint' => '10,2'),
            'coupon_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'status' => array('type' => 'VARCHAR', 'constraint' => '50', 'default' => 'pendente'),
            'created_at' => array('type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'),
            'updated_at' => array('type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'on update' => 'CURRENT_TIMESTAMP'),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('orders');
        // Adicionando a FK para coupons
        $this->db->query('ALTER TABLE `orders` ADD FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL');

        // Tabela `order_items`
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 5, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'order_id' => array('type' => 'INT', 'unsigned' => TRUE),
            'product_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'variation_id' => array('type' => 'INT', 'unsigned' => TRUE, 'null' => TRUE),
            'quantity' => array('type' => 'INT'),
            'unit_price' => array('type' => 'DECIMAL', 'constraint' => '10,2'),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('order_items');
        $this->db->query('ALTER TABLE `order_items` ADD FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE');
        $this->db->query('ALTER TABLE `order_items` ADD FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL');
        $this->db->query('ALTER TABLE `order_items` ADD FOREIGN KEY (`variation_id`) REFERENCES `product_variations`(`id`) ON DELETE SET NULL');

    }

    public function down()
    {
        // Remove as tabelas na ordem inversa para evitar erros de FK
        $this->dbforge->drop_table('order_items');
        $this->dbforge->drop_table('orders');
        $this->dbforge->drop_table('coupons');
        $this->dbforge->drop_table('inventory');
        $this->dbforge->drop_table('product_variations');
        $this->dbforge->drop_table('products');
    }
}
