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
            $Router->Call('UsernameIsAvailable', function() { UsernameIsAvailable($this); }, false, 'UsernameIsAvailable.10.1000');
            $Router->Call('SignUp',              function() { SignUp($this);              }, false, 'SignUp.10.1000');
            $Router->Call('SignIn',              function() { SignIn($this);              }, false, 'SignIn.10.1000');
            $Router->Call('ResetPassword',       function() { ResetPassword($this);       }, false, 'ResetPassword.10.1000');
            $Router->Call('SignInGoogle',        function() { SignInGoogle($this);        }, false, 'SignInGoogle.10.1000');
            $Router->Call('ChangePassword',      function() { ChangePassword($this);      }, true, 'ChangePassword.10.1000');

            $Router->Call('PostWrite',         function() { PostWrite($this);         }, true, 'PostWrite.10.2000');
            $Router->Call('PostList',          function() { PostList($this);          }, true, 'PostList.10.3000');
            $Router->Call('PostDelete',        function() { PostDelete($this);        }, true, 'PostDelete.10.2000');
            $Router->Call('PostReport',        function() { PostReport($this);        }, true, 'PostReport.10.2000');
            $Router->Call('PostTurnComment',   function() { PostTurnComment($this);   }, true, 'PostTurnComment.10.2000');
            $Router->Call('PostLike',          function() { PostLike($this);          }, true, 'PostLike.10.2000');
            $Router->Call('PostLikeList',      function() { PostLikeList($this);      }, true, 'PostLikeList.10.2000');
            $Router->Call('PostDetails',       function() { PostDetails($this);       }, true, 'PostDetails.10.2000');
            $Router->Call('PostComment',       function() { PostComment($this);       }, true, 'PostComment.10.2000');
            $Router->Call('PostCommentList',   function() { PostCommentList($this);   }, true, 'PostCommentList.10.2000');
            $Router->Call('PostCommentLike',   function() { PostCommentLike($this);   }, true, 'PostCommentLike.10.2000');
            $Router->Call('PostCommentDelete', function() { PostCommentDelete($this); }, true, 'PostCommentDelete.10.2000');
            $Router->Call('PostBookmark',      function() { PostBookmark($this);      }, true, 'PostBookmark.10.2000');
            $Router->Call('PostListInbox',     function() { PostListInbox($this);     }, true, 'PostListInbox.10.2000');
            $Router->Call('PostListCategory',  function() { PostListCategory($this);  }, true, 'PostListCategory.10.3000');
            $Router->Call('PostListBookmark',  function() { PostListBookmark($this);  }, true, 'PostListBookmark.10.3000');

            $Router->Call('ProfileGet',          function() { ProfileGet($this);          }, true, 'ProfileGet.10.3000');
            $Router->Call('ProfileGetPost',      function() { ProfileGetPost($this);      }, true, 'ProfileGetPost.10.3000');
            $Router->Call('ProfileGetComment',   function() { ProfileGetComment($this);   }, true, 'ProfileGetComment.10.3000');
            $Router->Call('ProfileGetLike',      function() { ProfileGetLike($this);      }, true, 'ProfileGetLike.10.3000');
            $Router->Call('ProfileSetEdit',      function() { ProfileSetEdit($this);      }, true, 'ProfileSetEdit.10.3000');
            $Router->Call('ProfileGetEdit',      function() { ProfileGetEdit($this);      }, true, 'ProfileGetEdit.10.3000');
            $Router->Call('ProfileCoverDelete',  function() { ProfileCoverDelete($this);  }, true, 'ProfileCoverDelete.10.3000');
            $Router->Call('ProfileAvatarDelete', function() { ProfileAvatarDelete($this); }, true, 'ProfileAvatarDelete.10.3000');
            $Router->Call('ProfilePostGet',      function() { ProfilePostGet($this);      }, true, 'ProfilePostGet.10.3000');
            $Router->Call('ProfileCommentGet',   function() { ProfileCommentGet($this);   }, true, 'ProfileCommentGet.10.3000');
            $Router->Call('ProfileLikeGet',      function() { ProfileLikeGet($this);      }, true, 'ProfileLikeGet.10.3000');

            $Router->Call('Follow',        function() { Follow($this);        }, true, 'Follow.10.3000');
            $Router->Call('FollowingList', function() { FollowingList($this); }, true, 'FollowingList.10.3000');
            $Router->Call('FollowersList', function() { FollowersList($this); }, true, 'FollowersList.10.3000');

            $Router->Call('SearchPeople',  function() { SearchPeople($this); },  true, 'SearchPeople.10.3000');
            $Router->Call('SearchTag',     function() { SearchTag($this); },     true, 'SearchTag.10.3000');
            $Router->Call('SearchTagList', function() { SearchTagList($this); }, true, 'SearchTagList.10.3000');

            $Router->Call('Notification',        function() { Notification($this);        }, true, 'Notification.10.3000');
            $Router->Call('NotificationList',    function() { NotificationList($this);    }, true, 'NotificationList.10.3000');
            $Router->Call('NotificationService', function() { NotificationService($this); }, true, 'NotificationService.10.3000');

            $Router->Call('Crash', function() { Crash($this); }, false, 'Crash.10.120000');

            $Router->Call('AdminStatus', function() { AdminStatus($this); }, false, 'AdminStatus.10.120000');

            $Router->Execute($this);
        }
    }
?>