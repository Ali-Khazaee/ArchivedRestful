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

            $Router->Call('PostWrite',         function() { PostWrite($this);         }, true, 'PostWrite.3.2000');
            $Router->Call('PostList',          function() { PostList($this);          }, true, 'PostList.5.3000');
            $Router->Call('PostDelete',        function() { PostDelete($this);        }, true, 'PostDelete.2.2000');
            $Router->Call('PostTurnComment',   function() { PostTurnComment($this);   }, true, 'PostTurnComment.2.2000');
            $Router->Call('PostLike',          function() { PostLike($this);          }, true, 'PostLike.5.2000');
            $Router->Call('PostLikeList',      function() { PostLikeList($this);      }, true, 'PostLikeList.5.2000');
            $Router->Call('PostDetails',       function() { PostDetails($this);       }, true, 'PostDetails.5.2000');
            $Router->Call('PostComment',       function() { PostComment($this);       }, true, 'PostComment.3.2000');
            $Router->Call('PostCommentList',   function() { PostCommentList($this);   }, true, 'PostCommentList.3.2000');
            $Router->Call('PostCommentLike',   function() { PostCommentLike($this);   }, true, 'PostCommentLike.5.2000');
            $Router->Call('PostCommentDelete', function() { PostCommentDelete($this); }, true, 'PostCommentDelete.3.2000');
            $Router->Call('PostBookMark',      function() { PostBookMark($this);      }, true, 'PostBookMark.3.2000');

            $Router->Call('ProfileGet',          function() { ProfileGet($this);          }, true, 'ProfileGet.2.3000');
            $Router->Call('ProfileGetPost',      function() { ProfileGetPost($this);      }, true, 'ProfileGetPost.2.3000');
            $Router->Call('ProfileGetComment',   function() { ProfileGetComment($this);   }, true, 'ProfileGetComment.2.3000');
            $Router->Call('ProfileGetLike',      function() { ProfileGetLike($this);      }, true, 'ProfileGetLike.2.3000');
            $Router->Call('ProfileSetEdit',      function() { ProfileSetEdit($this);      }, true, 'ProfileSetEdit.2.3000');
            $Router->Call('ProfileGetEdit',      function() { ProfileGetEdit($this);      }, true, 'ProfileGetEdit.2.3000');
            $Router->Call('ProfileCoverDelete',  function() { ProfileCoverDelete($this);  }, true, 'ProfileCoverDelete.2.3000');
            $Router->Call('ProfileAvatarDelete', function() { ProfileAvatarDelete($this); }, true, 'ProfileAvatarDelete.2.3000');

            $Router->Call('LastOnline', function() { LastOnline($this); }, true, 'LastOnline.1.120000');

            $Router->Execute($this);
        }
    }
?>