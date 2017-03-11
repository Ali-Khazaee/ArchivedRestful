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
            $Router->Call('ActivityWelcomeUsernameFree', function() { ActivityWelcomeUsernameFree($this); }, false, 'ActivityWelcomeUsernameFree.1.1000');
            $Router->Call('ActivityWelcomeEmailSign',    function() { ActivityWelcomeEmailSign($this);    }, false, 'ActivityWelcomeEmailSign.1.1000');
            $Router->Call('ActivityWelcomeSignIn',       function() { ActivityWelcomeSignIn($this);       }, false, 'ActivityWelcomeSignIn.1.1000');
            $Router->Call('ActivityWelcomeReset',        function() { ActivityWelcomeReset($this);        }, false, 'ActivityWelcomeReset.1.1000');
            $Router->Call('ActivityWelcomeSignInGoogle', function() { ActivityWelcomeSignInGoogle($this); }, false, 'ActivityWelcomeSignInGoogle.1.1000');

            $Router->Call('ActivityProfileEdit',                 function() { ActivityProfileEdit($this);                 }, true, 'ActivityProfileEdit.1.1000');
            $Router->Call('ActivityProfileEditSave',             function() { ActivityProfileEditSave($this);             }, true, 'ActivityProfileEditSave.1.1000');
            $Router->Call('ActivityProfileEditDeleteProfile',    function() { ActivityProfileEditDeleteProfile($this);    }, true, 'ActivityProfileEditDeleteProfile.1.1000');
            $Router->Call('ActivityProfileEditDeleteBackGround', function() { ActivityProfileEditDeleteBackGround($this); }, true, 'ActivityProfileEditDeleteBackGround.1.1000');

            $Router->Call('GeneralAdapterPost', function() { GeneralAdapterPost($this); }, true, 'GeneralAdapterPost.1.1000');

            $Router->Call('AdminGetTotalOnline', function() { AdminGetTotalOnline($this); }, true, 'AdminGetTotalOnline.1.1000');

            $Router->Call('MiscGetProfileImage',  function() { MiscGetProfileImage($this);  }, true, 'MiscGetProfileImage.1.1000');
            $Router->Call('MiscKeepClientOnline', function() { MiscKeepClientOnline($this); }, true, 'MiscKeepClientOnline.1.60000');

            $Router->Execute($this);
        }
    }
?>