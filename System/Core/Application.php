<?php
    if (!defined("ROOT")) { exit(); }

    class Application
    {
        public $DB;
        public $Auth;
        public $RateLimit;

        public function __construct()
        {
            $this->DB        = new DataBase();
            $this->Auth      = new Auth();
            $this->RateLimit = new RateLimit();

            $Router = new Router();
            $Router->Call('UsernameIsAvailable', function() { UsernameIsAvailable($this); }, false, 'UsernameIsAvailable.1.1000');
            $Router->Call('SignUp',              function() { SignUp($this);              }, false, 'SignUp.1.1000');
            $Router->Call('SignIn',              function() { SignIn($this);              }, false, 'SignIn.1.1000');
            $Router->Call('ResetPassword',       function() { ResetPassword($this);       }, false, 'ResetPassword.1.1000');
            $Router->Call('SignInGoogle',        function() { SignInGoogle($this);        }, false, 'SignInGoogle.1.1000');

            $Router->Call('GetProfile', function() { GetProfile($this); }, true, 'GetProfile.1.1000');

            $Router->Execute($this);
        }
    }
?>