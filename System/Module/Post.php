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

        JSON(["Status" => "Failed", "Message" => 1000, "CommentID" => $CommentID, "Time" => $Time]);
    }

    function CommentList($App)
    {
        $PostID = $_POST["PostID"];

        if (!isset($PostID) || empty($PostID))
            JSON(["Status" => "Failed", "Message" => 1]);

        $Comm = array();
        $CommentList = $App->DB->Find('comment', ['PostID' => $PostID], ['sort' => ['Time' => 1]])->toArray();

        foreach ($CommentList as $Comment)
        {
            $Username = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($Comment->OwnerID)])->toArray()[0]->Username;
            array_push($Comm, array("CommentID" => $Comment->_id, "OwnerID" => $Comment->OwnerID, "Username" => $Username, "Time" => $Comment->Time, "Message" => $Comment->Message, "Like" => $Comment->Like));
        }

        var_dump($Comm);
           
           
           
        //JSON(["Status" => "Failed", "Message" => 1000, "CommentID" => $CommentID, "Time" => $Time]);
    }
?>