<?php
    if (!defined("ROOT")) { exit(); }

    function CommentSend($App)
    {
        $PostID = $_POST["PostID"];
        $Message = $_POST["Message"];

        if (!isset($PostID) || empty($PostID))
            JSON(["Status" => "Failed", "Message" => 1]);

        if (!isset($Message) || empty($Message))
            JSON(["Status" => "Failed", "Message" => 2]);

        $Time = time();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $CommentID = $App->DB->Insert('comment', ['PostID' => $PostID, 'OwnerID' => $OwnerID, 'Time' => $Time, 'Message' => $Message, 'Like' => 0])->__toString();

        JSON(["Message" => 1000, "CommentID" => $CommentID, "Time" => $Time]);
    }

    function CommentList($App)
    {
        $PostID = $_POST["PostID"];
        $Skip = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $CommentTime = isset($_POST["CommentTime"]) ? $_POST["CommentTime"] : 0;

        if (!isset($PostID) || empty($PostID))
            JSON(["Status" => "Failed", "Message" => 1]);

        $Comm = array();

        if ($CommentTime)
            $CommentList = $App->DB->Find('comment', ['PostID' => $PostID, 'Time' => ['$gt' => (int)$CommentTime]], ['limit' => 8, 'sort' => ['Time' => 1]])->toArray();
        else
            $CommentList = $App->DB->Find('comment', ['PostID' => $PostID], ['skip' => $Skip, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($CommentList as $Comment)
        {
            $Username = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($Comment->OwnerID)])->toArray();

            if (isset($Username[0]))
            {
                $User = $Username[0]->Username;
                array_push($Comm, array("CommentID" => $Comment->_id->__toString(), "OwnerID" => $Comment->OwnerID->__toString(), "Username" => $User, "Time" => $Comment->Time, "Message" => $Comment->Message, "Like" => $Comment->Like));
            }
        }

        JSON(["Status" => "Success", "Message" => 1000, "Result" => json_encode($Comm)]);
    }

    function CommentLike($App)
    {
        
    }

    function CommentRemove($App)
    {
        $CommentID = $_POST["CommentID"];

        if (!isset($CommentID) || empty($CommentID))
            JSON(["Message" => 1]);

        $Query = ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "_id" => new MongoDB\BSON\ObjectID($CommentID)]]];

        $CommentList = $App->DB->Find('comment', $Query)->toArray();

        if (isset($CommentList[0]))
        {
            $App->DB->Remove('comment', $Query);
            JSON(["Message" => 1000]); 
        }

        JSON(["Message" => 999]); 
    }
?>