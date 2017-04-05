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

            $Router->Call('MomentList',   function() { MomentList($this);   }, true, 'MomentList.2.3000');
            $Router->Call('MomentWrite',  function() { MomentWrite($this);  }, true, 'MomentWrite.2.3000');
            $Router->Call('MomentDelete', function() { MomentDelete($this); }, true, 'MomentDelete.2.3000');

            $Router->Call('LikeList', function() { LikeList($this); }, true, 'LikeList.2.3000');
            $Router->Call('LikePost', function() { LikePost($this); }, true, 'LikePost.2.3000');

            $Router->Call('CommentList',   function() { CommentList($this);   }, true, 'CommentList.2.3000');
            $Router->Call('CommentPost',   function() { CommentPost($this);   }, true, 'CommentPost.2.3000');
            $Router->Call('CommentLike',   function() { CommentLike($this);   }, true, 'CommentLike.2.3000');
            $Router->Call('CommentDelete', function() { CommentDelete($this); }, true, 'CommentDelete.2.3000');

            $Router->Call('ProfileGet',          function() { ProfileGet($this);          }, true, 'ProfileGet.2.3000');
            $Router->Call('ProfileSet',          function() { ProfileSet($this);          }, true, 'ProfileSet.2.3000');
            $Router->Call('ProfileGetEdit',      function() { ProfileGetEdit($this);      }, true, 'ProfileGetEdit.2.3000');
            $Router->Call('ProfileCoverDelete',  function() { ProfileCoverDelete($this);  }, true, 'ProfileCoverDelete.2.3000');
            $Router->Call('ProfileAvatarDelete', function() { ProfileAvatarDelete($this); }, true, 'ProfileAvatarDelete.2.3000');

            $Router->Call('LastOnline', function() { LastOnline($this); }, true, 'LastOnline.1.120000');

            $Router->Execute($this);
        }
    }
?>