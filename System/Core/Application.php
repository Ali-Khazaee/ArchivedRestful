<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    class Application
    {
        // DataBase Instance
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

            // Account > Register
            $this->Router->POST('AccountRegister', function() { Account::Register($this); }, true);

            // Account > Login
            $this->Router->POST('AccountLogin', function() { Account::Login($this); }, true);

            // Account > Logout
            $this->Router->POST('AccountLogout', function() { Account::Logout($this); });

            // TESTING : Account > UploadAvatarImage
            $this->Router->POST('UploadAvatarImage', function() { Account::UploadAvatarImage($this); }, true);

            // Execute The Routing
            $this->Router->Execute($this);
        }
    }
?>