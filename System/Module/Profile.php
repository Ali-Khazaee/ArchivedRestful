<?php
    if (!defined("ROOT")) { exit(); }

    function ProfileGet($App)
    {
        $ID = isset($_POST["ID"]) ? new MongoDB\BSON\ObjectID($_POST["ID"]) : new MongoDB\BSON\ObjectID($App->Auth->ID);

        $Account = $App->DB->Find('account', ['_id' => $ID], ["projection" => ["_id" => 0, "Username" => 1, "Description" => 1, "Link" => 1, "Name" => 1, "Cover" => 1, "Avatar" => 1]])->toArray();

        $Post = $App->DB->Command(["count" => "post", "query" => ['OwnerID' => $ID]])->toArray()[0]->n;
        $Follower = $App->DB->Command(["count" => "follower", "query" => ['OwnerID' => $ID]])->toArray()[0]->n;
        $Following = $App->DB->Command(["count" => "following", "query" => ['OwnerID' => $ID]])->toArray()[0]->n;

        if (!isset($Post) || empty($Post))
            $Post = 0;

        if (!isset($Follower) || empty($Follower))
            $Follower = 0;

        if (!isset($Following) || empty($Following))
            $Following = 0;

        $Result = json_encode(array("Username"    => isset($Account[0]->Username)    ? $Account[0]->Username : "",
                                    "Description" => isset($Account[0]->Description) ? $Account[0]->Description : "",
                                    "Link"        => isset($Account[0]->Link)        ? $Account[0]->Link : "",
                                    "Cover"       => isset($Account[0]->Cover)       ? $Account[0]->Cover : "",
                                    "Avatar"      => isset($Account[0]->Avatar)      ? $Account[0]->Avatar : "",
                                    "Post"        => $Post,
                                    "Follower"    => $Follower,
                                    "Following"   => $Following));

        JSON(["Message" => 1000, "Result" => $Result]);
    }

    function ProfileGetPost($App)
    {
        $Result = array();
        $ID = new MongoDB\BSON\ObjectID($_POST["ID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $PostList = $App->DB->Find('post', ["OwnerID" => $ID], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($PostList as $Post)
        {
            $Account = $App->DB->Find('account', ['_id' => $Post->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "Avatar" => 1]])->toArray();

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

            array_push($Result, array("PostID"       => $Post->_id->__toString(),
                                      "OwnerID"      => $Post->OwnerID->__toString(),
                                      "Type"         => $Post->Type,
                                      "Category"     => $Post->Category,
                                      "Time"         => $Post->Time,
                                      "Comment"      => $Post->Comment,
                                      "Message"      => isset($Post->Message) ? $Post->Message : "",
                                      "Data"         => isset($Post->Data) ? $Post->Data : "",
                                      "Username"     => $Account[0]->Username,
                                      "Avatar"       => isset($Account[0]->Avatar) ? $Account[0]->Avatar : "",
                                      "Like"         => $Like,
                                      "LikeCount"    => $LikeCount,
                                      "CommentCount" => $CommentCount,
                                      "BookMark"     => $BookMark));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function ProfileGetComment($App)
    {
        $Result = array();
        $ID = new MongoDB\BSON\ObjectID($_POST["ID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $PostList = $App->DB->Find('post_comment', ["OwnerID" => $ID], ["projection" => ["_id" => 0, "PostID" => 1], 'skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($PostList as $PostID)
        {
            $Post = $App->DB->Find('post', ["_id" => $PostID->PostID])->toArray();

            if (!isset($Post))
                continue;

            $Account = $App->DB->Find('account', ['_id' => $Post[0]->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "Avatar" => 1]])->toArray();

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

            array_push($Result, array("PostID"       => $Post[0]->_id->__toString(),
                                      "OwnerID"      => $Post[0]->OwnerID->__toString(),
                                      "Type"         => $Post[0]->Type,
                                      "Category"     => $Post[0]->Category,
                                      "Time"         => $Post[0]->Time,
                                      "Comment"      => $Post[0]->Comment,
                                      "Message"      => isset($Post[0]->Message) ? $Post[0]->Message : "",
                                      "Data"         => isset($Post[0]->Data) ? $Post[0]->Data : "",
                                      "Username"     => $Account[0]->Username,
                                      "Avatar"       => isset($Account[0]->Avatar) ? $Account[0]->Avatar : "",
                                      "Like"         => $Like,
                                      "LikeCount"    => $LikeCount,
                                      "CommentCount" => $CommentCount,
                                      "BookMark"     => $BookMark));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function ProfileGetLike($App)
    {
        $Result = array();
        $ID = new MongoDB\BSON\ObjectID($_POST["ID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $PostList = $App->DB->Find('post_like', ["OwnerID" => $ID], ["projection" => ["_id" => 0, "PostID" => 1], 'skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($PostList as $PostID)
        {
            $Post = $App->DB->Find('post', ["_id" => $PostID->PostID])->toArray();

            if (!isset($Post))
                continue;

            $Account = $App->DB->Find('account', ['_id' => $Post[0]->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "Avatar" => 1]])->toArray();

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

            array_push($Result, array("PostID"       => $Post[0]->_id->__toString(),
                                      "OwnerID"      => $Post[0]->OwnerID->__toString(),
                                      "Type"         => $Post[0]->Type,
                                      "Category"     => $Post[0]->Category,
                                      "Time"         => $Post[0]->Time,
                                      "Comment"      => $Post[0]->Comment,
                                      "Message"      => isset($Post[0]->Message) ? $Post[0]->Message : "",
                                      "Data"         => isset($Post[0]->Data) ? $Post[0]->Data : "",
                                      "Username"     => $Account[0]->Username,
                                      "Avatar"       => isset($Account[0]->Avatar) ? $Account[0]->Avatar : "",
                                      "Like"         => $Like,
                                      "LikeCount"    => $LikeCount,
                                      "CommentCount" => $CommentCount,
                                      "BookMark"     => $BookMark));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function ProfileGetEdit($App)
    {
        $Account = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($App->Auth->ID)])->toArray();

        if (isset($Account[0]->Lat) && isset($Account[0]->Lon))
            $Position = $Account[0]->Latitude . ":" . $Account[0]->Longitude;
        else
            $Position = "";

        $Result = json_encode(array("Username"    => isset($Account[0]->Username)    ? $Account[0]->Username : "",
                                    "Description" => isset($Account[0]->Description) ? $Account[0]->Description : "",
                                    "Link"        => isset($Account[0]->Link)        ? $Account[0]->Link : "",
                                    "Position"    => $Position,
                                    "Location"    => isset($Account[0]->Location)    ? $Account[0]->Location : "",
                                    "Email"       => isset($Account[0]->Email)       ? $Account[0]->Email : "",
                                    "Cover"       => isset($Account[0]->Cover)       ? $Account[0]->Cover : "",
                                    "Avatar"      => isset($Account[0]->Avatar)      ? $Account[0]->Avatar : ""));

        JSON(["Message" => 1000, "Result" => $Result]);
    }

    function ProfileSetEdit($App)
    {
        $Username        = isset($_POST["Username"])    ? strtolower($_POST["Username"]) : "";
        $Description     = isset($_POST["Description"]) ? urldecode($_POST["Description"]) : "";
        $Link            = isset($_POST["Link"])        ? strtolower($_POST["Link"]) : "";
        $Location        = isset($_POST["Location"])    ? urldecode($_POST["Location"]) : "";
        $Position        = isset($_POST["Position"])    ? $_POST["Position"] : "";
        $Email           = isset($_POST["Email"])       ? urldecode($_POST["Email"]) : "";
        $Latitude        = "";
        $Longitude       = "";

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (strlen($Username) < 3)
            JSON(["Message" => 2]);

        if (strlen($Username) > 32)
            JSON(["Message" => 3]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 4]);

        if (!isset($Email) || empty($Email))
            JSON(["Message" => 5]);

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            JSON(["Message" => 6,]);

        if (strlen($Email) > 64)
            JSON(["Message" => 7]);

        if (strlen($Description) > 150)
            JSON(["Message" => 8]);

        if (!empty($Position))
        {
            $Split = explode(":", $Position);

            if (count($Split) != 2)
                JSON(["Message" => 9]);

            $Latitude = $Split[0];
            $Longitude = $Split[1];
        }

        $Cover = "";
        $Avatar = "";
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Account = $App->DB->Find('account', ['_id' => $OwnerID])->toArray();

        if (isset($_FILES['Avatar']))
        {
            $FileName = $_FILES['Avatar']['name'];
            $FileSize = $_FILES['Avatar']['size'];
            $FileTemp = $_FILES['Avatar']['tmp_name'];

            if ($FileSize < 2097152 && in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("jpeg", "jpg", "png")))
            {
                Upload::DeleteFile($Account[0]->Avatar);
                $Server = Upload::GetBestServer();

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $OwnerID, "FILE" => new CurlFile($FileTemp, "image/jpeg")]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                $Avatar = $Server . $URL;
            }
        }
        
        if (isset($_FILES['Cover']))
        {
            $FileName = $_FILES['Cover']['name'];
            $FileSize = $_FILES['Cover']['size'];
            $FileTemp = $_FILES['Cover']['tmp_name'];
            $FileType = $_FILES['Cover']['type'];

            if ($FileSize < 2097152 && in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("jpeg", "jpg")))
            {
                Upload::DeleteFile($Account[0]->Cover);
                $Server = Upload::GetBestServer();

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $OwnerID, "FILE" => new CurlFile($FileTemp, "image/jpeg")]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                $Cover = $Server . $URL;
            }
        }

        if ($Avatar == "" && isset($Account[0]->Avatar))
            $Avatar = $Account[0]->Avatar;

        if ($Cover == "" && isset($Account[0]->Cover))
            $Cover = $Account[0]->Cover;

        $App->DB->Update('account', ['_id' => $OwnerID], ['$set' => ['Username'    => $Username,
                                                                     'Description' => $Description,
                                                                     'Link'        => $Link,
                                                                     'Email'       => $Email,
                                                                     'Latitude'    => $Latitude,
                                                                     'Longitude'   => $Longitude,
                                                                     'Location'    => $Location,
                                                                     'Avatar'      => $Avatar,
                                                                     'Cover'       => $Cover]]);

        JSON(["Message" => 1000]);
    }

    function ProfileCoverDelete($App)
    {
        $ID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $Account = $App->DB->Find('account', ['_id' => $ID], ["projection" => ["_id" => 0, "Cover" => 1]])->toArray();

        Upload::DeleteFile($Account[0]->Cover);

        $App->DB->Update('account', ['_id' => $ID], ['$set' => ['Cover' => ""]]);

        JSON(["Message" => 1000]);
    }

    function ProfileAvatarDelete($App)
    {
        $ID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $Account = $App->DB->Find('account', ['_id' => $ID], ["projection" => ["_id" => 0, "Avatar" => 1]])->toArray();

        Upload::DeleteFile($Account[0]->Avatar);

        $App->DB->Update('account', ['_id' => $ID], ['$set' => ['Avatar' => ""]]);

        JSON(["Message" => 1000]);
    }
?>