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
                curl_setopt($Channel, CURLOPT_URL, $DataServerURL . "UploadImage");
                curl_setopt($Channel, CURLOPT_POST, true);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["Password" => Upload::GetServerToken($DataServerID), "FileImage" => new CurlFile($File['tmp_name'], "image/jpeg")]);
                $ServerResult = json_decode(curl_exec($Channel));
                curl_close($Channel);

                if ($ServerResult->Result != 1000)
                    JSON(["Message" => 2]);

                array_push($Data, $ServerResult->Path);
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
                curl_setopt($Channel, CURLOPT_URL, $DataServerURL . "UploadVideo");
                curl_setopt($Channel, CURLOPT_POST, true);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["Password" => Upload::GetServerToken($DataServerID), "FileVideo" => new CurlFile($File['tmp_name'], "video/mp4")]);
                $ServerResult = json_decode(curl_exec($Channel));
                curl_close($Channel);

                if ($ServerResult->Result != 1000)
                    JSON(["Message" => 2]);

                array_push($Data, $ServerResult->Path);
            }
        }
        elseif ($Type == 3)
        {
            array_push($Data, $Link);
        }

        if (empty($Category) || $Category > 16 || $Category < 1)
            $Category = 100;

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Result = array("OwnerID" => $OwnerID, "Type" => $Type, "Category" => $Category, "Time" => time(), "Comment" => true);

        if (!empty($Message))
            $Result["Message"] = $Message;

        if ($Type == 1 || $Type == 2)
            $Result["DataServer"] = $DataServerID;

        if (!empty($Data))
            $Result["Data"] = $Data;

        $PostID = $App->DB->Insert('post', $Result);

        if (!empty($Message))
        {
            preg_match_all('/@(\w+)/', $Message, $UsernameList);
            $UsernameList = explode(',', implode(',', $UsernameList[1]));

            if (count($UsernameList) > 0)
            {
                for ($X = 0; $X < count($UsernameList); $X++)
                {
                    $Account = $App->DB->Find('account', ['Username' => $UsernameList[$X]], ["projection" => ["_id" => 1]])->toArray();

                    if (empty($Account))
                        continue;

                    if ($Account[0]->_id != $OwnerID)
                        $App->DB->Insert('notification', ["OwnerID" => $Account[0]->_id, "SenderID" => $OwnerID, "PostID" => $PostID, "Seen" => 0, "Type" => 1, "Time" => time()]);
                }
            }

            preg_match_all('/#(\w+)/u', $Message, $HashTagList);
            $HashTagList = explode(',', implode(',', $HashTagList[1]));

            if (count($HashTagList) > 0)
            {
                for ($X = 0; $X < count($HashTagList); $X++)
                {
                    if (!isset($App->DB->Find('tag', ['Tag' => $HashTagList[$X]])->toArray()[0]))
                        $App->DB->Insert('tag', ['Tag' => strtolower($HashTagList[$X])]);
                }
            }
        }

        JSON(["Message" => 1000, "Result" => GetSinglePost($App, $PostID, $OwnerID)]);
    }

    function GetSinglePost($App, $PostID, $RequestID)
    {
        $Post = $App->DB->Find('post', ["_id" => $PostID])->toArray();

        if (!isset($Post) || empty($Post))
            return null;

        $Account = $App->DB->Find('account', ['_id' => $Post[0]->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

        if (isset($App->DB->Find('post_like', ['$and' => [["OwnerID" => $RequestID, "PostID" => $Post[0]->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
            $Like = true;
        else
            $Like = false;

        if (isset($App->DB->Find('post_bookmark', ['$and' => [["OwnerID" => $RequestID, "PostID" => $Post[0]->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
            $BookMark = true;
        else
            $BookMark = false;

        $LikeCount = $App->DB->Command(["count" => "post_like", "query" => ['PostID' => $Post[0]->_id]])->toArray()[0]->n;

        if (!isset($LikeCount) || empty($LikeCount))
            $LikeCount = 0;

        $CommentCount = $App->DB->Command(["count" => "post_comment", "query" => ['PostID' => $Post[0]->_id]])->toArray()[0]->n;

        if (!isset($CommentCount) || empty($CommentCount))
            $CommentCount = 0;

        if (isset($Account[0]->AvatarServer) && isset($Account[0]->Avatar))
            $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer) . $Account[0]->Avatar;
        else
            $AvatarServerURL = "";

        $PostData = array();

        if ($Post[0]->Type == 1 || $Post[0]->Type == 2)
        {
            if (isset($Post[0]->DataServer))
                $DataServerURL = Upload::GetServerURL($Post[0]->DataServer);
            else
                $DataServerURL = "";

            if (isset($Post[0]->Data))
            {
                foreach ($Post[0]->Data As $Data)
                    array_push($PostData, $DataServerURL . $Data);
            }
        }
        elseif ($Post[0]->Type == 3)
        {
            $PostData = $Post[0]->Data;
        }

        if (isset($App->DB->Find('follow', ['$and' => [["OwnerID" => $RequestID, "Follower" => $Post[0]->OwnerID]]], ["projection" => ["_id" => 1]])->toArray()[0]))
            $Follow = true;
        else
            $Follow = false;

        $Result = array("PostID"       => $Post[0]->_id->__toString(),
                        "OwnerID"      => $Post[0]->OwnerID->__toString(),
                        "Type"         => $Post[0]->Type,
                        "Category"     => $Post[0]->Category,
                        "Time"         => $Post[0]->Time,
                        "Comment"      => $Post[0]->Comment,
                        "Message"      => isset($Post[0]->Message) ? $Post[0]->Message : "",
                        "Data"         => $PostData,
                        "Username"     => $Account[0]->Username,
                        "Avatar"       => $AvatarServerURL,
                        "Like"         => $Like,
                        "LikeCount"    => $LikeCount,
                        "CommentCount" => $CommentCount,
                        "BookMark"     => $BookMark,
                        "Follow"       => $Follow);

        return json_encode($Result);
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

                if (isset($Post->Data))
                {
                    foreach ($Post->Data As $Data)
                        array_push($PostData, $DataServerURL . $Data);
                }
            }
            elseif ($Post->Type == 3)
            {
                $PostData = $Post->Data;
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

    function PostReport($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $App->DB->Insert('report', ["PostID" => new MongoDB\BSON\ObjectID($_POST["PostID"]), "OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "Time" => time()]);

        JSON(["Message" => 1000]); 
    }

    function PostDelete($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Post = $App->DB->Find('post', ['$and' => [["OwnerID" => $OwnerID, "_id" => $PostID]]], ["projection" => ["Message" => 1]])->toArray();

        if (isset($Post) && !empty($Post))
        {
            $App->DB->Remove('notification', ["PostID" => $PostID]);

            foreach ($App->DB->Find('post_comment', ["PostID" => $PostID], ["projection" => ["_id" => 1]])->toArray() as $Comment)
            {
                $App->DB->Remove('post_comment_like', ["CommentID" => $Comment->_id]);
                $App->DB->Remove('notification', ["CommentID" => $Comment->_id]);
            }

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
        {
            $App->DB->Remove('post_like', $Query);

            $Post = $App->DB->Find('post', ["_id" => $PostID], ["projection" => ["OwnerID" => 1]])->toArray();

            if ($Post[0]->OwnerID != $OwnerID)
                $App->DB->Remove('notification', ["OwnerID" => $Post[0]->OwnerID, "SenderID" => $OwnerID, "PostID" => $PostID, "Type" => 2]);
        }
        else
        {
            $App->DB->Insert('post_like', ["OwnerID" => $OwnerID, "PostID" => $PostID, "Time" => time()]);

            $Post = $App->DB->Find('post', ["_id" => $PostID], ["projection" => ["OwnerID" => 1]])->toArray();

            if ($Post[0]->OwnerID != $OwnerID)
                $App->DB->Insert('notification', ["OwnerID" => $Post[0]->OwnerID, "SenderID" => $OwnerID, "PostID" => $PostID, "Type" => 2, "Seen" => 0, "Time" => time()]);
        }

        JSON(["Message" => 1000]); 
    }

    function PostLikeList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $LikeList = $App->DB->Find('post_like', ['PostID' => new MongoDB\BSON\ObjectID($_POST["PostID"])], ["projection" => ["_id" => 0, "OwnerID" => 1, "Time" => 1], 'skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($LikeList as $Like)
        {
            $Account = $App->DB->Find('account', ['_id' => $Like->OwnerID], ["projection" => ["Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer) . $Account[0]->Avatar;
            else
                $AvatarServerURL = "";

            if (isset($App->DB->Find('follow', ['$and' => [["OwnerID" => $OwnerID, "Follower" => $Account[0]->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $IsFollow = true;
            else
                $IsFollow = false;

            array_push($Result, array("OwnerID" => $Like->OwnerID->__toString(), "Username" => $Account[0]->Username, "Avatar" => $AvatarServerURL, "Follow" => $IsFollow, "Time" => $Like->Time));
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

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $CommentID = $App->DB->Insert('post_comment', ['PostID' => $PostID, 'OwnerID' => $OwnerID, 'Time' => time(), 'Message' => $Message]);

        $Post = $App->DB->Find('post', ["_id" => $PostID], ["projection" => ["OwnerID" => 1]])->toArray();

        if ($Post[0]->OwnerID != $OwnerID)
            $App->DB->Insert('notification', ["OwnerID" => $Post[0]->OwnerID, "SenderID" => $OwnerID, "PostID" => $PostID, "CommentID" => $CommentID, "Seen" => 0, "Type" => 5, "Time" => time()]);

        preg_match_all('/@(\w+)/', $Message, $UsernameList);
        $UsernameList = explode(',', implode(',', $UsernameList[1]));

        if (count($UsernameList) > 0)
        {
            for ($X = 0; $X < count($UsernameList); $X++)
            {
                $Account = $App->DB->Find('account', ['Username' => $UsernameList[$X]], ["projection" => ["_id" => 1]])->toArray();

                if (empty($Account))
                    continue;

                if ($Account[0]->_id != $OwnerID)
                    $App->DB->Insert('notification', ["OwnerID" => $Account[0]->_id, "SenderID" => $OwnerID, "PostID" => $PostID, "CommentID" => $CommentID, "Type" => 6, "Seen" => 0, "Time" => time()]);
            }
        }

        JSON(["Message" => 1000, "CommentID" => $CommentID->__toString()]);
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
        {
            $App->DB->Remove('post_comment_like', $Query);

            $Post = $App->DB->Find('post', ["_id" => $CommentID], ["projection" => ["OwnerID" => 1]])->toArray();

            if ($Post[0]->OwnerID != $OwnerID)
                $App->DB->Remove('notification', ["OwnerID" => $Post[0]->OwnerID, "SenderID" => $OwnerID, "CommentID" => $CommentID, "Type" => 4]);
        }
        else
        {
            $App->DB->Insert('post_comment_like', ["OwnerID" => $OwnerID, "CommentID" => $CommentID]);

            $Post = $App->DB->Find('post', ["_id" => $CommentID], ["projection" => ["_id" => 1, "OwnerID" => 1]])->toArray();

            if ($Post[0]->OwnerID != $OwnerID)
                $App->DB->Insert('notification', ["OwnerID" => $Post[0]->OwnerID, "SenderID" => $OwnerID, "PostID" => $Post[0]->_id, "CommentID" => $CommentID, "Type" => 4, "Seen" => 0, "Time" => time()]);
        }

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

        if (isset($App->DB->Find('post_comment', $Query, ["projection" => ["Message" => 1]])->toArray()[0]))
        {
            $App->DB->Remove('post_comment', $Query);
            $App->DB->Remove('notification', ["CommentID" => $CommentID]);
            JSON(["Message" => 1000]); 
        }

        if (isset($App->DB->Find('post', ['$and' => [["OwnerID" => $OwnerID, "PostID" => new MongoDB\BSON\ObjectID($_POST["PostID"])]]], ["projection" => ["_id" => 1]])->toArray()[0]))
        {
            $App->DB->Remove('post_comment', ["CommentID" => $CommentID]);
            $App->DB->Remove('notification', ["CommentID" => $CommentID]);
            JSON(["Message" => 1000]); 
        }

        JSON(["Message" => 3]); 
    }

    function PostBookmark($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);

        $Query = ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]];
        $BookMark = $App->DB->Find('post_bookmark', $Query, ["projection" => ["_id" => 1]])->toArray();

        if (isset($BookMark[0]))
        {
            $IsBookmark = false;
            $App->DB->Remove('post_bookmark', $Query);
        }
        else
        {
            $IsBookmark = true;
            $App->DB->Insert('post_bookmark', ["OwnerID" => $OwnerID, "PostID" => $PostID, "Time" => time()]);
        }

        JSON(["Message" => 1000, "Bookmark" => $IsBookmark]); 
    }

    function PostListInbox($App)
    {
        $ResultList = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $FollowList = $App->DB->Find('follow', ["OwnerID" => $OwnerID], ["projection" => ["_id" => 0, "Follower" => 1]])->toArray();

        foreach ($FollowList as $Follow)
        {
            $PostList = $App->DB->Find('post', ['OwnerID' => $Follow->Follower], ["projection" => ["_id" => 1, "Time" => 1]])->toArray();

            foreach ($PostList as $Post)
                $ResultList[$Post->Time] = $Post->_id;
        }

        $Count = 0;
        $Result = array();
        arsort($ResultList);
        $ResultList = array_values($ResultList);
        $Skip = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;

        for ($I = $Skip; $I < count($ResultList); $I++)
        {
            if ($Count > 7)
                break;

            $Count++;

            $Post = $App->DB->Find('post', ["_id" => $ResultList[$I]])->toArray();
            $Account = $App->DB->Find('account', ['_id' => $Post[0]->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($App->DB->Find('post_like', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post[0]->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Like = true;
            else
                $Like = false;

            if (isset($App->DB->Find('post_bookmark', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post[0]->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
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
            elseif ($Post[0]->Type == 3)
            {
                $PostData = $Post[0]->Data;
            }

            if (isset($App->DB->Find('follow', ['$and' => [["OwnerID" => $OwnerID, "Follower" => $Post[0]->OwnerID]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Follow = true;
            else
                $Follow = false;

            array_push($Result, array("PostID"       => $Post[0]->_id->__toString(),
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
                                      "BookMark"     => $BookMark,
                                      "Follow"       => $Follow));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function PostListCategory($App)
    {
        $Result = array();
        $Category = isset($_POST["CatType"]) ? $_POST["CatType"] : 17;
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        if (isset($_POST["Time"]))
            $PostList = $App->DB->Find('post', ["Category" => $Category, 'Time' => ['$gt' => (int) $_POST["Time"]]], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();
        else
            $PostList = $App->DB->Find('post', ["Category" => $Category], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

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

                if (isset($Post->Data))
                {
                    foreach ($Post->Data As $Data)
                        array_push($PostData, $DataServerURL . $Data);
                }
            }
            elseif ($Post->Type == 3)
            {
                $PostData = $Post->Data;
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

    function PostListBookmark($App)
    {
        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $BookMarkList = $App->DB->Find('post_bookmark', ["OwnerID" => $OwnerID], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($BookMarkList as $BookMark)
        {
            $Post = $App->DB->Find('post', ["_id" => $BookMark->PostID])->toArray()[0];
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

                if (isset($Post->Data))
                {
                    foreach ($Post->Data As $Data)
                        array_push($PostData, $DataServerURL . $Data);
                }
            }
            elseif ($Post->Type == 3)
            {
                $PostData = $Post->Data;
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
?>