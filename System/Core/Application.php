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

            $this->SetLog = new SetLog($this); // @TODO in esmesh Bayad Avaz She!

            $this->Router->POST('Register', function() { Account::Register($this); }, true, 'Register.1.60000');
            $this->Router->POST('Login',    function() { Account::Login($this);    }, true);
            $this->Router->POST('Logout',   function() { Account::Logout($this);   } );

            $this->Router->POST('UpdateProfileImage', function() { Account::UpdateProfileImage($this); }, true, 'UpdateProfileImage.100.1000');

            $this->Router->Execute($this);
        }
    }
?>