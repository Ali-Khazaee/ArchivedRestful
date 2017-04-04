<?php
    if (!defined("ROOT")) { exit(); }

    function ActivityWorldCommentSend($App)
    {
        $PostID = $_POST["PostID"];
        $Message = $_POST["Message"];

        if (!isset($PostID) || empty($PostID))
            JSON(["Message" => 1]);

        if (!isset($Message) || empty($Message))
            JSON(["Message" => 2]);

        $Time = time();
        $CommentID = $App->DB->Insert('post_world_comment', ['PostID' => new MongoDB\BSON\ObjectID($PostID), 'OwnerID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'Time' => $Time, 'Message' => $Message])->__toString();

        JSON(["Message" => 1000, "CommentID" => $CommentID, "Time" => $Time]);
    }

    function ActivityWorldCommentList($App)
    {
        $Comm = array();
        $PostID = $_POST["PostID"];
        $SkipCount = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $CommentTime = isset($_POST["CommentTime"]) ? $_POST["CommentTime"] : 0;

        if (!isset($PostID) || empty($PostID))
            JSON(["Message" => 1]);

        if ($CommentTime)
            $CommentList = $App->DB->Find('post_world_comment', ['PostID' => new MongoDB\BSON\ObjectID($PostID), 'Time' => ['$gt' => (int)$CommentTime]], ['limit' => 8, 'sort' => ['Time' => 1]])->toArray();
        else
            $CommentList = $App->DB->Find('post_world_comment', ['PostID' => new MongoDB\BSON\ObjectID($PostID)], ['skip' => $SkipCount, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($CommentList as $Comment)
        {
            $Username = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($Comment->OwnerID)])->toArray();

            if (isset($Username[0]))
            {
                $User = $Username[0]->Username;
                $CommentID = new MongoDB\BSON\ObjectID($Comment->_id->__toString());
                $Like = $App->DB->Find('post_world_comment_like', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "CommentID" => new MongoDB\BSON\ObjectID($CommentID)]]])->toArray();

                if (isset($Like[0]))
                    $Like = true;
                else
                    $Like = false;

                $LikeCount = $App->DB->Find('post_world_comment_like', ["CommentID" => new MongoDB\BSON\ObjectID($CommentID)])->toArray();

                if (isset($LikeCount[0]))
                    $LikeCount = count($LikeCount);
                else
                    $LikeCount = 0;

                array_push($Comm, array("CommentID" => $Comment->_id->__toString(), "OwnerID" => $Comment->OwnerID->__toString(), "Username" => $User, "Time" => $Comment->Time, "Message" => $Comment->Message, "LikeCount" => $LikeCount, "Like" => $Like));
            }
        }

        JSON(["Message" => 1000, "Result" => json_encode($Comm)]);
    }

    function ActivityWorldCommentLike($App)
    {
        $CommentID = $_POST["CommentID"];

        if (!isset($CommentID) || empty($CommentID))
            JSON(["Message" => 1]);

        $Query = ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "CommentID" => new MongoDB\BSON\ObjectID($CommentID)]]];
        $Comment = $App->DB->Find('post_world_comment_like', $Query)->toArray();

        if (isset($Comment[0]))
            $App->DB->Remove('post_world_comment_like', $Query, ['limit' => 1]);
        else
            $App->DB->Insert('post_world_comment_like', ["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "CommentID" => new MongoDB\BSON\ObjectID($CommentID)]);

        JSON(["Message" => 1000]); 
    }

    function ActivityWorldCommentRemove($App)
    {
        $PostID = $_POST["PostID"];
        $CommentID = $_POST["CommentID"];

        if (!isset($CommentID) || empty($CommentID))
            JSON(["Message" => 1]);

        if (!isset($PostID) || empty($PostID))
            JSON(["Message" => 2]);

        $Query = ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "_id" => new MongoDB\BSON\ObjectID($CommentID)]]];
        $CommentList = $App->DB->Find('post_world_comment', $Query)->toArray();

        if (isset($CommentList[0]))
        {
            $App->DB->Remove('post_world_comment', $Query);
            JSON(["Message" => 1000]); 
        }

        $PostOnwer = $App->DB->Find('post_world', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => new MongoDB\BSON\ObjectID($PostID)]]])->toArray();

        if (isset($PostOnwer[0]))
        {
            $App->DB->Remove('post_world_comment', $Query);
            JSON(["Message" => 1000]); 
        }

        JSON(["Message" => 999]); 
    }
?>