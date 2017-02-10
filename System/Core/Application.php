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
            $Router->Call('ActivityHomeWrite', function() { ActivityHomeWrite($this); }, true, 'ActivityHomeWrite.1.3000');

            $Router->Call('CommentSend',   function() { CommentSend($this);   }, true, 'CommentSend.1.3000');
            $Router->Call('CommentList',   function() { CommentList($this);   }, true, 'CommentList.1.1500');
            $Router->Call('CommentLike',   function() { CommentLike($this);   }, true, 'CommentLike.1.1000');
            $Router->Call('CommentRemove', function() { CommentRemove($this); }, true, 'CommentRemove.1.3000');

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