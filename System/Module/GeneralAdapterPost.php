<?php
    if (!defined("ROOT")) { exit(); }

    function ActivityWorld($App)
    {
        $PostData = array();
        $PostTime = isset($_POST["PostTime"]) ? $_POST["PostTime"] : 0;
        $SkipCount = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;

        if ($PostTime)
            $PostList = $App->DB->Find('post_world', ['Time' => ['$gt' => (int) $PostTime]], ['skip' => $SkipCount, 'limit' => 5, 'sort' => ['Time' => -1]])->toArray();
        else
            $PostList = $App->DB->Find('post_world', [], ['skip' => $SkipCount, 'limit' => 5, 'sort' => ['Time' => -1]])->toArray();

        foreach ($PostList as $Post)
        {
            $Username = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($Post->OwnerID)])->toArray();

            if (isset($Username[0]))
            {
                $Username = $Username[0]->Username;
                $PostID = new MongoDB\BSON\ObjectID($Post->_id->__toString());
                $Like = $App->DB->Find('post_world_like', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => new MongoDB\BSON\ObjectID($PostID)]]])->toArray();

                if (isset($Like[0]))
                    $Like = true;
                else
                    $Like = false;

                $LikeCount = $App->DB->Find('post_world_like', ["PostID" => new MongoDB\BSON\ObjectID($PostID)])->toArray();
                $SeenCount = $App->DB->Find('post_world_seen', ["PostID" => new MongoDB\BSON\ObjectID($PostID)])->toArray();
                $CommentCount = $App->DB->Find('post_world_comment', ["PostID" => new MongoDB\BSON\ObjectID($PostID)])->toArray();

                if (isset($LikeCount[0]))
                    $LikeCount = count($LikeCount);
                else
                    $LikeCount = 0;

                if (isset($SeenCount[0]))
                    $SeenCount = count($SeenCount);
                else
                    $SeenCount = 0;

                if (isset($CommentCount[0]))
                    $CommentCount = count($CommentCount);
                else
                    $CommentCount = 0;

                array_push($PostData, array("PostID" => $Post->_id->__toString(), "OwnerID" => $Post->OwnerID->__toString(), "Username" => $Username, "Time" => $Post->Time, "Message" => $Post->Message, "Data" => $Post->Data, "State" => $Post->State, "LikeCount" => $LikeCount, "SeenCount" => $SeenCount, "CommentCount" => $CommentCount, "Like" => $Like));
            }
        }

        JSON(["Message" => 1000, "Result" => json_encode($PostData)]);
    }
?>