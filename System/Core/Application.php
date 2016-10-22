<?php
    if (!defined("ROOT")) { exit(); }

    class Application
    {
        public $DB;
        public $Auth;
        public $Router;
        public $Upload;
        public $RateLimit;

        public function __construct()
        {
            $this->DB = new DataBase();
            $this->Auth = new Auth();
            $this->Router = new Router();
            $this->Upload = new Upload();
            $this->RateLimit = new RateLimit();

            $this->Log = new Log($this); // @TODO in esmesh Bayad Avaz She!

            $this->Router->POST('Register', function() { Account::Register($this); }, true);
            $this->Router->POST('Login',    function() { Account::Login($this);    }, true);
            $this->Router->POST('Logout',   function() { Account::Logout($this);   }      );

            $this->Router->POST('UpdateProfileImage', function() { Account::UpdateProfileImage($this); }, true);

            $this->Router->Execute($this);
        }
    }
?>