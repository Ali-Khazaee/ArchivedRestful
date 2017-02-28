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
            $Router->Call('ActivityWorld',              function() { ActivityWorld($this);              }, true, 'ActivityWorld.1.3000');
            $Router->Call('ActivityWorldLike',          function() { ActivityWorldLike($this);          }, true, 'ActivityWorldLike.1.3000');
            $Router->Call('ActivityWorldLikeList',      function() { ActivityWorldLikeList($this);      }, true, 'ActivityWorldLikeList.1.3000');
            $Router->Call('ActivityWorldCommentSend',   function() { ActivityWorldCommentSend($this);   }, true, 'ActivityWorldCommentSend.1.3000');
            $Router->Call('ActivityWorldCommentList',   function() { ActivityWorldCommentList($this);   }, true, 'ActivityWorldCommentList.1.1500');
            $Router->Call('ActivityWorldCommentLike',   function() { ActivityWorldCommentLike($this);   }, true, 'ActivityWorldCommentLike.1.1000');
            $Router->Call('ActivityWorldCommentRemove', function() { ActivityWorldCommentRemove($this); }, true, 'ActivityWorldCommentRemove.1.3000');
            $Router->Call('ActivityWorldWrite',         function() { ActivityWorldWrite($this);         }, true, 'ActivityWorldWrite.1.3000');

            $Router->Call('ActivityProfile',            function() { ActivityProfile($this);            }, true, 'ActivityProfile.1.3000');

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