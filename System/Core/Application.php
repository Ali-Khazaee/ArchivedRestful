<?php
    class Application
    {
        // DataBase Variable
        public $DB;

        // Router Instance
        protected $Router;

        public $Auth;

        public function __construct()
        {
            // Connecting To DataBase
            $this->DB = new DataBase();

            // Create New Router
            $this->Router = new Router();

            // Create new Auth
            $this->Auth = new Auth();

            // Route For Account Register
            $this->Router->POST('AccountRegister', function() { Account::Register($this); });

            // Route For Account Login
            $this->Router->POST('AccountLogin', function() { Account::Login($this); });

            // just for testing token auth
            $this->Router->POST('UpdateUsername', function() { Account::UpdateUsername($this); });


            // Execute The Routing
            $this->Router->Execute();
        }
    }
?>