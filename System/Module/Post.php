<?php
    if (!defined("ROOT")) { exit(); }

    function PostWrite($App)
    {
        $Message = isset($_POST["Message"]) ? urldecode($_POST["Message"]) : "";
        $Category = isset($_POST["Category"]) ? $_POST["Category"] : 100;
        $Type = isset($_POST["Type"]) ? $_POST["Type"] : 0;
        $Link = isset($_POST["Link"]) ? $_POST["Link"] : "";

        if ($Type == 0 && strlen($Message) < 20)
            JSON(["Message" => 1]);

        if (strlen($Message) > 150)
            $Message = mb_substr($Message, 0, 150);

        $NewLine = 0;
        $ResultMessage = "";

        for ($I = 0; $I < strlen($Message); $I++)
        {
            if (ord($Message[$I]) == 10)
                $NewLine++;

            if ($NewLine > 4 && ord($Message[$I]) == 10)
                continue;

            $ResultMessage .= $Message[$I];
        }

        $Data = array();
        $Message = $ResultMessage;
        $DataServerID = Upload::GetBestServerID();
        $DataServerURL = Upload::GetServerURL($DataServerID);

        if ($Type == 1)
        {
            $ImageCount = 0;

            foreach ($_FILES AS $File)
            {
                if ($ImageCount > 2)
                    continue;

                if ($File['size'] > 2097152)
                    continue;

                $ImageCount++;

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $DataServerURL);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($DataServerID), "FOLDER" => $App->Auth->ID, "FILE" => new CurlFile($File['tmp_name'], "image/jpeg")]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                array_push($Data, $URL);
            }
        }
        elseif ($Type == 2)
        {
            $VideoCount = 0;

            foreach ($_FILES AS $File)
            {
                if ($VideoCount > 0)
                    continue;

                if ($File['size'] > 15728640)
                    continue;

                $VideoCount++;

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $DataServerURL);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_VIDEO", "TOKEN" => Upload::GetServerToken($DataServerID), "FOLDER" => $App->Auth->ID, "FILE" => new CurlFile($File['tmp_name'], "video/mp4")]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                array_push($Data, $URL);
            }
        }
        elseif ($Type == 3)
        {
            array_push($Data, $Link);
        }

        if (empty($Category) || $Category > 16 || $Category < 1)
            $Category = 100;

        $Result = array("OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "Type" => $Type, "Category" => $Category, "Time" => time(), "Comment" => true);

        if (!empty($Message))
            $Result["Message"] = $Message;

        if ($Type == 1 || $Type == 2)
            $Result["DataServer"] = $DataServerID;

        if (!empty($Data))
            $Result["Data"] = $Data;

        $App->DB->Insert('post', $Result);

        JSON(["Message" => 1000]);
    }

    function PostList($App)
    {
        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        if (isset($_POST["Time"]))
            $PostList = $App->DB->Find('post', ['Time' => ['$gt' => (int) $_POST["Time"]]], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();
        else
            $PostList = $App->DB->Find('post', [], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($PostList as $Post)
        {
            $Account = $App->DB->Find('account', ['_id' => $Post->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($App->DB->Find('post_like', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Like = true;
            else
                $Like = false;

            if (isset($App->DB->Find('post_bookmark', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $BookMark = true;
            else
                $BookMark = false;
            
            $LikeCount = $App->DB->Command(["count" => "post_like", "query" => ['PostID' => $Post->_id]])->toArray()[0]->n;

            if (!isset($LikeCount) || empty($LikeCount))
                $LikeCount = 0;

            $CommentCount = $App->DB->Command(["count" => "post_comment", "query" => ['PostID' => $Post->_id]])->toArray()[0]->n;

            if (!isset($CommentCount) || empty($CommentCount))
                $CommentCount = 0;

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            $PostData = array();

            if ($Post->Type == 1 || $Post->Type == 2)
            {
                if (isset($Post->DataServer))
                    $DataServerURL = Upload::GetServerURL($Post->DataServer);
                else
                    $DataServerURL = "";

                foreach ($Post->Data As $Data)
                    array_push($PostData, $DataServerURL . $Data);
            }

            if (isset($App->DB->Find('follow', ['$and' => [["OwnerID" => $OwnerID, "Follower" => $Post->OwnerID]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Follow = true;
            else
                $Follow = false;

            array_push($Result, array("PostID"       => $Post->_id->__toString(),
                                      "OwnerID"      => $Post->OwnerID->__toString(),
                                      "Type"         => $Post->Type,
                                      "Category"     => $Post->Category,
                                      "Time"         => $Post->Time,
                                      "Comment"      => $Post->Comment,
                                      "Message"      => isset($Post->Message) ? $Post->Message : "",
                                      "Data"         => $PostData,
                                      "Username"     => $Account[0]->Username,
                                      "Avatar"       => isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : "",
                                      "Like"         => $Like,
                                      "LikeCount"    => $LikeCount,
                                      "CommentCount" => $CommentCount,
                                      "BookMark"     => $BookMark,
                                      "Follow"       => $Follow));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function PostDelete($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);

        if (isset($App->DB->Find('post', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "_id" => $PostID]]], ["projection" => ["_id" => 1]])->toArray()[0]))
        {
            foreach ($App->DB->Find('post_comment', ["PostID" => $PostID], ["projection" => ["_id" => 1]])->toArray() as $Comment)
                $App->DB->Remove('post_comment_like', ["CommentID" => $Comment->_id]);

            $App->DB->Remove('post', ["_id" => $PostID]);
            $App->DB->Remove('post_like', ["PostID" => $PostID]);
            $App->DB->Remove('post_comment', ["PostID" => $PostID]);
            $App->DB->Remove('post_bookmark', ["PostID" => $PostID]);
            JSON(["Message" => 1000]);
        }

        JSON(["Message" => 2]);
    }

    function PostTurnComment($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);

        if (isset($App->DB->Find('post', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "_id" => $PostID]]], ["projection" => ["_id" => 1]])->toArray()[0]))
        {
            $Result = $App->DB->Find('post', ["_id" => $PostID], ["projection" => ["_id" => 0, "Comment" => 1]])->toArray();

            if (!empty($Result) && $Result[0]->Comment)
                $App->DB->Update('post', ["_id" => $PostID], ['$set' => ['Comment' => false]]);
            else
                $App->DB->Update('post', ["_id" => $PostID], ['$set' => ['Comment' => true]]);

            JSON(["Message" => 1000]);
        }

        JSON(["Message" => 2]);
    }

    function PostLike($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Query = ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]];

        if (isset($App->DB->Find('post_like', $Query, ["projection" => ["_id" => 1]])->toArray()[0]))
            $App->DB->Remove('post_like', $Query);
        else
            $App->DB->Insert('post_like', ["OwnerID" => $OwnerID, "PostID" => $PostID, "Time" => time()]);

        JSON(["Message" => 1000]); 
    }

    function PostLikeList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);
        
        $Result = array();
        $LikeList = $App->DB->Find('post_like', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"])], ["projection" => ["_id" => 0, "OwnerID" => 1, "Time" => 1], 'skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($LikeList as $Like)
        {
            $Account = $App->DB->Find('account', ['_id' => $Like->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            array_push($Result, array("OwnerID" => $Like->OwnerID->__toString(), "Username" => $Account[0]->Username, "Avatar" => (isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : ""), "Time" => $Like->Time));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function PostDetails($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $Post = $App->DB->Find('post', ["_id" => new MongoDB\BSON\ObjectID($_POST["PostID"])])->toArray();

        if (!isset($Post) || empty($Post))
            JSON(["Message" => 2]);

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Account = $App->DB->Find('account', ['_id' => $Post[0]->OwnerID], ['projection' => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

        if (isset($App->DB->Find('post_like', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post[0]->_id]]])->toArray()[0]))
            $Like = true;
        else
            $Like = false;

        if (isset($App->DB->Find('post_bookmark', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post[0]->_id]]])->toArray()[0]))
            $BookMark = true;
        else
            $BookMark = false;
        
        $LikeCount = $App->DB->Command(["count" => "post_like", "query" => ['PostID' => $Post[0]->_id]])->toArray()[0]->n;

        if (!isset($LikeCount) || empty($LikeCount))
            $LikeCount = 0;

        $CommentCount = $App->DB->Command(["count" => "post_comment", "query" => ['PostID' => $Post[0]->_id]])->toArray()[0]->n;

        if (!isset($CommentCount) || empty($CommentCount))
            $CommentCount = 0;

        if (isset($Account[0]->AvatarServer))
            $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
        else
            $AvatarServerURL = "";

        $PostData = array();

        if ($Post[0]->Type == 1 || $Post[0]->Type == 2)
        {
            if (isset($Post[0]->DataServer))
                $DataServerURL = Upload::GetServerURL($Post[0]->DataServer);
            else
                $DataServerURL = "";

            foreach ($Post[0]->Data As $Data)
                array_push($PostData, $DataServerURL . $Data);
        }

        $Result = array("PostID"       => $Post[0]->_id->__toString(),
                        "OwnerID"      => $Post[0]->OwnerID->__toString(),
                        "Type"         => $Post[0]->Type,
                        "Category"     => $Post[0]->Category,
                        "Time"         => $Post[0]->Time,
                        "Comment"      => $Post[0]->Comment,
                        "Message"      => isset($Post[0]->Message) ? $Post[0]->Message : "",
                        "Data"         => $PostData,
                        "Username"     => $Account[0]->Username,
                        "Avatar"       => isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : "",
                        "Like"         => $Like,
                        "LikeCount"    => $LikeCount,
                        "CommentCount" => $CommentCount,
                        "BookMark"     => $BookMark);

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function PostComment($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        if (!isset($_POST["Message"]) || empty($_POST["Message"]))
            JSON(["Message" => 2]);

        $Post = $App->DB->Find('post', ["_id" => new MongoDB\BSON\ObjectID($_POST["PostID"])], ["projection" => ["_id" => 0, "Comment" => 1]])->toArray();

        if (!isset($Post) || empty($Post))
            JSON(["Message" => 3]);

        if ($Post[0]->Comment == false)
            JSON(["Message" => 4]);
        
        $Message = $_POST["Message"];

        if (strlen($Message) > 150)
            $Message = mb_substr($Message, 0, 150);

        $CommentID = $App->DB->Insert('post_comment', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"]), 'OwnerID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'Time' => time(), 'Message' => $Message])->__toString();

        JSON(["Message" => 1000, "CommentID" => $CommentID]);
    }

    function PostCommentList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        if (isset($_POST["CommentTime"]))
            $CommentList = $App->DB->Find('post_comment', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"]), 'Time' => ['$gt' => (int) $_POST["CommentTime"]]], ['limit' => 8, 'sort' => ['Time' => 1]])->toArray();
        else
            $CommentList = $App->DB->Find('post_comment', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"])], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($CommentList as $Comment)
        {
            $Account = $App->DB->Find('account', ['_id' => $Comment->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($App->DB->Find('post_comment_like', ['$and' => [["OwnerID" => $OwnerID, "CommentID" => $Comment->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Like = true;
            else
                $Like = false;

            $LikeCount = $App->DB->Command(["count" => "post_comment_like", "query" => ['CommentID' => $Comment->_id]])->toArray()[0]->n;

            if (!isset($LikeCount) || empty($LikeCount))
                $LikeCount = 0;

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            array_push($Result, array("CommentID" => $Comment->_id->__toString(), "OwnerID" => $Comment->OwnerID->__toString(), "Time" => $Comment->Time, "Message" => $Comment->Message, "LikeCount" => $LikeCount, "Like" => $Like, "Username" => $Account[0]->Username, "Avatar" => (isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : "")));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function PostCommentLike($App)
    {
        if (!isset($_POST["CommentID"]) || empty($_POST["CommentID"]))
            JSON(["Message" => 1]);

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $CommentID = new MongoDB\BSON\ObjectID($_POST["CommentID"]);

        $Query = ['$and' => [["OwnerID" => $OwnerID, "CommentID" => $CommentID]]];
        $Comment = $App->DB->Find('post_comment_like', $Query, ["projection" => ["_id" => 1]])->toArray();

        if (isset($Comment[0]))
            $App->DB->Remove('post_comment_like', $Query);
        else
            $App->DB->Insert('post_comment_like', ["OwnerID" => $OwnerID, "CommentID" => $CommentID]);

        JSON(["Message" => 1000]); 
    }

    function PostCommentDelete($App)
    {
        if (!isset($_POST["CommentID"]) || empty($_POST["CommentID"]))
            JSON(["Message" => 1]);

        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 2]);

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $CommentID = new MongoDB\BSON\ObjectID($_POST["CommentID"]);
        $Query = ['$and' => [["OwnerID" => $OwnerID, "_id" => $CommentID]]];

        if (isset($App->DB->Find('post_comment', $Query, ["projection" => ["_id" => 1]])->toArray()[0]))
        {
            $App->DB->Remove('post_comment', $Query);
            JSON(["Message" => 1000]); 
        }

        if (isset($App->DB->Find('post', ['$and' => [["OwnerID" => $OwnerID, "PostID" => new MongoDB\BSON\ObjectID($_POST["PostID"])]]], ["projection" => ["_id" => 1]])->toArray()[0]))
        {
            $App->DB->Remove('post_comment', ["CommentID" => $CommentID]);
            JSON(["Message" => 1000]); 
        }

        JSON(["Message" => 3]); 
    }

    function PostBookMark($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);

        $Query = ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]];
        $BookMark = $App->DB->Find('post_bookmark', $Query, ["projection" => ["_id" => 1]])->toArray();

        if (isset($BookMark[0]))
            $App->DB->Remove('post_bookmark', $Query);
        else
            $App->DB->Insert('post_bookmark', ["OwnerID" => $OwnerID, "PostID" => $PostID]);

        JSON(["Message" => 1000]); 
    }
?>