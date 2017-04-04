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
            $Router->Call('UsernameIsAvailable', function() { UsernameIsAvailable($this); }, false, 'UsernameIsAvailable.5.1000');
            $Router->Call('SignUp',              function() { SignUp($this);              }, false, 'SignUp.5.1000');
            $Router->Call('SignIn',              function() { SignIn($this);              }, false, 'SignIn.5.1000');
            $Router->Call('ResetPassword',       function() { ResetPassword($this);       }, false, 'ResetPassword.5.1000');
            $Router->Call('SignInGoogle',        function() { SignInGoogle($this);        }, false, 'SignInGoogle.5.1000');

            $Router->Call('MomentList',  function() { MomentList($this);  }, true, 'MomentList.2.3000');
            $Router->Call('MomentWrite', function() { MomentWrite($this); }, true, 'MomentWrite.2.3000');

            $Router->Call('LikeList', function() { LikeList($this); }, true, 'LikeList.2.3000');
            $Router->Call('LikePost', function() { LikePost($this); }, true, 'LikePost.2.3000');

            $Router->Call('GetProfile',     function() { GetProfile($this);     }, true, 'GetProfile.5.1000');
            $Router->Call('GetProfileEdit', function() { GetProfileEdit($this); }, true, 'GetProfileEdit.5.1000');
            $Router->Call('SetProfileEdit', function() { SetProfileEdit($this); }, true, 'SetProfileEdit.5.1000');

            $Router->Execute($this);
        }
    }
?>