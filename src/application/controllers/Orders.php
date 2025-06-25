<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('order_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
    }

    /**
     * Exibe a página de gerenciamento de produtos e a lista de produtos.
     */
    public function index()
    {
        $data['orders'] = $this->order_model->get_all_orders();
        $this->load->view('orders/index_view', $data);
    }

}
