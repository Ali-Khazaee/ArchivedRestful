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
            $this->DB        = new DataBase();
            $this->Auth      = new Auth();
            $this->Logger    = new Logger($this);
            $this->Upload    = new Upload();
            $this->RateLimit = new RateLimit();

            $this->Router = new Router();
            $this->Router->Call('SignUp',             function() { SignUp($this);             }, false, 'SignUp.1.60000');
            $this->Router->Call('SignIn',             function() { SignIn($this);             }, false, 'SignIn.1.5000');
            $this->Router->Call('SignOut',            function() { SignOut($this);            }, true,  'SignOut.1.1000');
            $this->Router->Call('UpdateProfileImage', function() { UpdateProfileImage($this); }, false, 'UpdateProfileImage.1.3000');
            $this->Router->Execute($this);
        }
    }
?>