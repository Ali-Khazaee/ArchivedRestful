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
        public $Router;

        // Upload Instance
        public $Upload;

        public function __construct()
        {
            // Connecting To DataBase
            $this->DB = new DataBase();

            // Create New Router
            $this->Router = new Router();

            // Create New Auth
            $this->Auth = new Auth();

            // Create New Auth
            $this->Upload = new Upload();

            // Account > Register
            $this->Router->POST('AccountRegister', function() { Account::Register($this); }, true);

            // Account > Login
            $this->Router->POST('AccountLogin', function() { Account::Login($this); }, true);

            // Account > Logout
            $this->Router->POST('AccountLogout', function() { Account::Logout($this); });

            // Account > Upload
            $this->Router->POST('UploadAvatarImage', function()
            {
                var_dump($_FILES);
            }, true);

            // Execute The Routing
            $this->Router->Execute($this);
        }
    }
?>