<?php
    if (!defined("ROOT")) { exit(); }

    function LikeList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);
        
        $List = array();
        $Skip = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $Likes = $App->DB->Find('like', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"])], ['skip' => $Skip, 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($Likes as $Like)
        {
            $Username = $App->DB->Find('account', ['_id' => $Like->OwnerID])->toArray();

            if (isset($Username[0]))
                array_push($List, array("OwnerID" => $Like->OwnerID->__toString(), "Username" => $Username[0]->Username, "Avatar" => (isset($Username[0]->Avatar) ? $Username[0]->Avatar : ""), "Time" => $Like->Time));
        }

        JSON(["Message" => 1000, "Result" => json_encode($List)]);
    }

    function LikePost($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Query = ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]];

        if (isset($App->DB->Find('like', $Query)->toArray()[0]))
            $App->DB->Remove('like', $Query, ['limit' => 1]);
        else
            $App->DB->Insert('like', ["OwnerID" => $OwnerID, "PostID" => $PostID, "Time" => time()]);

        JSON(["Message" => 1000]); 
    }

    function CommentPost($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        if (!isset($_POST["Message"]) || empty($_POST["Message"]))
            JSON(["Message" => 2]);

        $CommentID = $App->DB->Insert('comment', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"]), 'OwnerID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'Time' => time(), 'Message' => $_POST["Message"]])->__toString();

        JSON(["Message" => 1000, "CommentID" => $CommentID]);
    }

    function CommentList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $Comment     = array();
        $Skip        = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $CommentTime = isset($_POST["CommentTime"]) ? $_POST["CommentTime"] : 0;

        if ($CommentTime)
            $CommentList = $App->DB->Find('comment', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"]), 'Time' => ['$gt' => (int) $CommentTime]], ['limit' => 8, 'sort' => ['Time' => 1]])->toArray();
        else
            $CommentList = $App->DB->Find('comment', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"])], ['skip' => $Skip, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($CommentList as $Com)
        {
            $Username = $App->DB->Find('account', ['_id' => $Com->OwnerID])->toArray();

            if (isset($Username[0]))
            {
                $Like = $App->DB->Find('like', ['$and' => [["OwnerID" => $Com->OwnerID, "CommentID" => $Com->_id]]])->toArray();

                if (isset($Like[0]))
                    $Like = true;
                else
                    $Like = false;

                $LikeCount = $App->DB->Command(["count" => "like", "query" => ['CommentID' => $Com->_id]])->toArray()[0]->n;

                if (!isset($LikeCount) || empty($LikeCount))
                    $LikeCount = 0;

                array_push($Comment, array("CommentID" => $Com->_id->__toString(), "OwnerID" => $Com->OwnerID->__toString(), "Username" => $Username[0]->Username, "Time" => $Com->Time, "Message" => $Com->Message, "LikeCount" => $LikeCount, "Like" => $Like, "Avatar" => (isset($Username[0]->Avatar) ? $Username[0]->Avatar : "")));
            }
        }

        JSON(["Message" => 1000, "Result" => json_encode($Comment)]);
    }

    function CommentLike($App)
    {
        if (!isset($_POST["CommentID"]) || empty($_POST["CommentID"]))
            JSON(["Message" => 1]);

        $Query = ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "CommentID" => new MongoDB\BSON\ObjectID($_POST["CommentID"])]]];
        $Comment = $App->DB->Find('like', $Query)->toArray();

        if (isset($Comment[0]))
            $App->DB->Remove('like', $Query, ['limit' => 1]);
        else
            $App->DB->Insert('like', ["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "CommentID" => new MongoDB\BSON\ObjectID($_POST["CommentID"])]);

        JSON(["Message" => 1000]); 
    }

    function CommentDelete($App)
    {
        if (!isset($_POST["CommentID"]) || empty($_POST["CommentID"]))
            JSON(["Message" => 1]);

        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 2]);

        $Query = ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "_id" => new MongoDB\BSON\ObjectID($_POST["CommentID"])]]];

        if (isset($App->DB->Find('comment', $Query)->toArray()[0]))
        {
            $App->DB->Remove('comment', $Query);
            JSON(["Message" => 1000]); 
        }

        if (isset($App->DB->Find('post', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => new MongoDB\BSON\ObjectID($_POST["PostID"])]]])->toArray()[0]))
        {
            $App->DB->Remove('comment', $Query);
            JSON(["Message" => 1000]); 
        }

        JSON(["Message" => 3]); 
    }

    function LastOnline($App)
    {
        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($App->Auth->ID)], ['$set' => ['LastOnline' => time()]]);
    }
?>