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

        // RateLimit Instance
        public $RateLimit;

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

            // Create New RateLimit
            $this->RateLimit = new RateLimit();

            // Create New Log
            $this->Log = new Log($this);

            // Account > Register
            $this->Router->POST('Register', function() { Account::Register($this); }, true);

            // Account > Login
            $this->Router->POST('Login', function() { Account::Login($this); }, true);

            // Account > Logout
            $this->Router->POST('Logout', function() { Account::Logout($this); });

            // Account > Update Profile Image
            $this->Router->POST('UpdateProfileImage', function() { Account::UpdateProfileImage($this); }, true);

            // Execute The Routing
            $this->Router->Execute($this);
        }
    }
?>