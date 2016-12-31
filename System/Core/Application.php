<?php
    if (!defined("ROOT")) { exit(); }

    class Application
    {
        public $DB;
        public $Auth;
        public $Upload;
        public $RateLimit;

        public function __construct()
        {
            $this->DB        = new DataBase();
            $this->Auth      = new Auth();
            $this->Upload    = new Upload();
            $this->RateLimit = new RateLimit();

            $Router = new Router();
            $Router->Call('UsernameIsFree', function() { UsernameIsFree($this); }, false, 'UsernameIsFree.1.4000');
            $Router->Call('SignUp',         function() { SignUp($this);         }, false, 'SignUp.1.1000');
            $Router->Call('SignIn',         function() { SignIn($this);         }, false, 'SignIn.1.5000');
            $Router->Call('CategoryList',   function() { CategoryList($this);   }, false, 'CategoryList.1.5000');
            $Router->Execute($this);
        }
    }
?>