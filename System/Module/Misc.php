<?php
    if (!defined("ROOT")) { exit(); }

    function LikeList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);
        
        $List = array();
        $Skip = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);

        $Likes = $App->DB->Find('like', ['PostID' => $PostID], ['skip' => $Skip, 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($Likes as $Like)
        {
            $Username = $App->DB->Find('account', ['_id' => $Like->OwnerID])->toArray();

            if (isset($Username[0]))
                array_push($List, array("OwnerID" => $Like->OwnerID->__toString(), "Username" => $Username[0]->Username, "URL" => (isset($Username[0]->Profile) ? $Username[0]->Profile : ""), "Time" => $Like->Time));
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

        $Post = $App->DB->Find('like', $Query)->toArray();

        if (isset($Post[0]))
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
        $Comment = array();
        $PostID = $_POST["PostID"];
        $Skip = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $CommentTime = isset($_POST["CommentTime"]) ? $_POST["CommentTime"] : 0;

        if (!isset($PostID) || empty($PostID))
            JSON(["Message" => 1]);

        if ($CommentTime)
            $CommentList = $App->DB->Find('comment', ['PostID' => new MongoDB\BSON\ObjectID($PostID), 'Time' => ['$gt' => (int) $CommentTime]], ['limit' => 8, 'sort' => ['Time' => 1]])->toArray();
        else
            $CommentList = $App->DB->Find('comment', ['PostID' => new MongoDB\BSON\ObjectID($PostID)], ['skip' => $Skip, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

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

                $LikeCount = $App->DB->Find('like', ["CommentID" => $Com->_id])->toArray();

                if (isset($LikeCount[0]))
                    $LikeCount = count($LikeCount);
                else
                    $LikeCount = 0;

                array_push($Comment, array("CommentID" => $Com->_id->__toString(), "OwnerID" => $Com->OwnerID->__toString(), "Username" => $Username[0]->Username, "Time" => $Com->Time, "Message" => $Com->Message, "LikeCount" => $LikeCount, "Like" => $Like, "Profile" => (isset($Username[0]->Profile) ? $Username[0]->Profile : "")));
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

        $PostOnwer = $App->DB->Find('post', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => new MongoDB\BSON\ObjectID($_POST["PostID"])]]])->toArray();

        if (isset($PostOnwer[0]))
        {
            $App->DB->Remove('comment', $Query);
            JSON(["Message" => 1000]); 
        }

        JSON(["Message" => 999]); 
    }
?>