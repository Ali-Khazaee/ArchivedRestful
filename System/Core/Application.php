<?php
    class Application
    {
        // DataBase Variable
        public $DB;

        // Auth Instance
        public $Auth;

        // Router Instance
        protected $Router;

        public function __construct()
        {
            // Connecting To DataBase
            $this->DB = new DataBase();

            // Create New Router
            $this->Router = new Router();

            // Create New Auth
            $this->Auth = new Auth();

            // Authenticate
            $this->Router->POST('Authenticate', function() { $this->Auth->Authenticate($this); }, true);

            // Account > Register
            $this->Router->POST('AccountRegister', function() { Account::Register($this); }, true);

            // Account > Login
            $this->Router->POST('AccountLogin', function() { Account::Login($this); }, true);

            // Account > Updata Username
            $this->Router->POST('AccountUpdateUsername', function() { Account::UpdateUsername($this); });

            // Execute The Routing
            $this->Router->Execute($this);
        }
    }
?>