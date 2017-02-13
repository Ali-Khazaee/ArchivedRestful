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
                $Like = $App->DB->Find('post_world_like', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => $PostID]]])->toArray();

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

    function ActivityWorldLike($App)
    {
        $PostID = $_POST["PostID"];

        if (!isset($PostID) || empty($PostID))
            JSON(["Message" => 1]);

        $Query = ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => new MongoDB\BSON\ObjectID($PostID)]]];
        $Post = $App->DB->Find('post_world_like', $Query)->toArray();

        if (isset($Post[0]))
            $App->DB->Remove('post_world_like', $Query, ['limit' => 1]);
        else
            $App->DB->Insert('post_world_like', ["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "PostID" => new MongoDB\BSON\ObjectID($PostID)]);

        JSON(["Message" => 1000]); 
    }

    function ActivityWorldCommentSend($App)
    {
        $PostID = $_POST["PostID"];
        $Message = $_POST["Message"];

        if (!isset($PostID) || empty($PostID))
            JSON(["Message" => 1]);

        if (!isset($Message) || empty($Message))
            JSON(["Message" => 2]);

        $Time = time();
        $CommentID = $App->DB->Insert('post_world_comment', ['PostID' => $PostID, 'OwnerID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'Time' => $Time, 'Message' => $Message])->__toString();

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
            $CommentList = $App->DB->Find('post_world_comment', ['PostID' => $PostID, 'Time' => ['$gt' => (int)$CommentTime]], ['limit' => 8, 'sort' => ['Time' => 1]])->toArray();
        else
            $CommentList = $App->DB->Find('post_world_comment', ['PostID' => $PostID], ['skip' => $SkipCount, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($CommentList as $Comment)
        {
            $Username = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($Comment->OwnerID)])->toArray();

            if (isset($Username[0]))
            {
                $User = $Username[0]->Username;
                $CommentID = new MongoDB\BSON\ObjectID($Comment->_id->__toString());
                $Like = $App->DB->Find('post_world_comment_like', ['$and' => [["OwnerID" => new MongoDB\BSON\ObjectID($App->Auth->ID), "CommentID" => $CommentID]]])->toArray();

                if (isset($Like[0]))
                    $Like = true;
                else
                    $Like = false;

                $LikeCount = $App->DB->Find('post_world_comment_like', ["CommentID" => $CommentID])->toArray();

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

    function ActivityWorldWrite($App)
    {
        $Message  = isset($_POST["Message"])  ? $_POST["Message"]  : NULL;
        $Category = isset($_POST["Category"]) ? $_POST["Category"] : NULL;
        $State    = isset($_POST["State"])    ? $_POST["State"]    : NULL;
        $LinkURL  = isset($_POST["LinkURL"])  ? $_POST["LinkURL"]  : NULL;

        if (!isset($State) || empty($State))
            JSON(["Message" => 1]);

        $Data = array();

        if ($State == 1)
        {
            $ImageCount = 0;

            foreach ($_FILES AS $File)
            {
                if ($ImageCount > 2)
                    continue;

                $FileName = $File['name'];
                $FileSize = $File['size'];
                $FileTemp = $File['tmp_name'];
                $FileType = $File['type'];

                if (!in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("jpeg", "jpg")))
                    continue;

                if ($FileType != "image/jpeg")
                    continue;

                if ($FileSize > 2097152)
                    continue;

                $ImageCount++;
                $Server = Upload::GetBestServer();

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $App->Auth->ID, "FILE" => new CurlFile($FileTemp, $FileType)]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                array_push($Data, ($Server . $URL));
            }
        }
        elseif ($State == 2)
        {
            $VideoCount = 0;

            foreach ($_FILES AS $File)
            {
                if ($VideoCount > 0)
                    continue;

                $FileName = $File['name'];
                $FileSize = $File['size'];
                $FileTemp = $File['tmp_name'];
                $FileType = $File['type'];

                if (!in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("mp4")))
                    continue;

                if ($FileType != "video/mp4")
                    continue;

                if ($FileSize > 5242880)
                    continue;

                $VideoCount++;
                $Server = Upload::GetBestServer();

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_VIDEO", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $App->Auth->ID, "FILE" => new CurlFile($FileTemp, $FileType)]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                $Data = $Server . $URL;
            }
        }
        elseif ($State == 3)
        {
            $Data = $LinkURL;
        }

        if ($Category == NULL || $Category > 17 || $Category < 0)
            $Category = 0;

        $App->DB->Insert('post_world', ['OwnerID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'State' => $State, 'Data' => $Data, 'Message' => $Message, 'Category' => $Category, 'Time' => time()]);
    }
?>