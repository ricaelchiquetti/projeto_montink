<?php

class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Apenas permite a execução em ambiente de desenvolvimento
        if (ENVIRONMENT !== 'development') {
            show_error('Migrations are only available in the development environment.', 403);
        }
        $this->load->library('migration');
    }

    public function index()
    {
        if ($this->migration->current() === FALSE)
        {
            show_error($this->migration->error_string());
        }
        else
        {
            echo 'Migrations ran successfully!';
        }
    }

    // Função para reverter todas as migrations
    public function reset()
    {
        if ($this->migration->version(0) === FALSE)
        {
            show_error($this->migration->error_string());
        }
        else
        {
            echo 'Migrations reset successfully!';
        }
    }
}
