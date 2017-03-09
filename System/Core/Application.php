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
            $Router->Call('ActivityWelcomeUsernameFree', function() { ActivityWelcomeUsernameFree($this); }, true, 'ActivityWelcomeUsernameFree.1.1000');
            $Router->Call('ActivityWelcomeEmailSign',    function() { ActivityWelcomeEmailSign($this);    }, true, 'ActivityWelcomeEmailSign.1.1000');
            $Router->Call('ActivityWelcomeSignIn',       function() { ActivityWelcomeSignIn($this);       }, true, 'ActivityWelcomeSignIn.1.1000');
            $Router->Call('ActivityWelcomeReset',        function() { ActivityWelcomeReset($this);        }, true, 'ActivityWelcomeReset.1.1000');
            $Router->Call('ActivityWelcomeSignInGoogle', function() { ActivityWelcomeSignInGoogle($this); }, true, 'ActivityWelcomeSignInGoogle.1.1000');

            $Router->Call('GeneralAdapterPost', function() { GeneralAdapterPost($this); }, true, 'GeneralAdapterPost.1.1000');

            $Router->Execute($this);
        }
    }
?>