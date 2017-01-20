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
            $Router->Call('UsernameIsFree', function() { UsernameIsFree($this); }, false, 'UsernameIsFree.1.3000');
            $Router->Call('SignUp',         function() { SignUp($this);         }, false, 'SignUp.1.1000');
            $Router->Call('SignIn',         function() { SignIn($this);         }, false, 'SignIn.1.2000');
            $Router->Call('CategoryList',   function() { CategoryList($this);   }, true,  'CategoryList.1.2000');
            $Router->Call('CategorySave',   function() { CategorySave($this);   }, true,  'CategorySave.1.2000');
            $Router->Call('ProfileGet',     function() { ProfileGet($this);     }, true,  'ProfileGet.1.2000');
            $Router->Call('ProfileSet',     function() { ProfileSet($this);     }, true,  'ProfileSet.1.2000');
            $Router->Execute($this);
        }
    }
?>