<?php
    class Application
    {
        // DataBase Variable
        public $DB;

        // Router Instance
        protected $Router;

        public function __construct()
        {
            // Connecting To DataBase
            $this->DB = new DataBase();

            // Create New Router
            $this->Router = new Router();

            // Route For Account Register
            $this->Router->POST(CONFIG_BASE_ROUTE . 'AccountRegister', function() { Account::Register($this); });

            // Route For Account Login
            $this->Router->POST(CONFIG_BASE_ROUTE . 'AccountLogin', function() { Account::Login($this); });

            // Execute The Routing
            $this->Router->Execute();
        }
    }
?>