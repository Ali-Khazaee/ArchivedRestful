<?php
    if (!defined("ROOT")) { exit(); }

    function GetProfile($App)
    {
        $ID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $Account = $App->DB->Find('account', ['_id' => $ID])->toArray();

        $Post = $App->DB->Command(["count" => "post", "query" => ['OwnerID' => $ID]])->toArray()[0]->n;
        $Follower = $App->DB->Command(["count" => "follower", "query" => ['OwnerID' => $ID]])->toArray()[0]->n;
        $Following = $App->DB->Command(["count" => "following", "query" => ['OwnerID' => $ID]])->toArray()[0]->n;

        if (!$Post)
            $Post = 0;

        if (!$Follower)
            $Follower = 0;

        if (!$Following)
            $Following = 0;

        $Result = json_encode(array("Username"    => isset($Account[0]->Username) ? $Account[0]->Username : "",
                                    "Description" => isset($Account[0]->Description) ? $Account[0]->Description : "",
                                    "Link"        => isset($Account[0]->Link) ? $Account[0]->Link : "",
                                    "Name"        => isset($Account[0]->Name) ? $Account[0]->Name : "",
                                    "BackGround"  => isset($Account[0]->ImageBackGround) ? $Account[0]->ImageBackGround : "",
                                    "Profile"     => isset($Account[0]->ImageProfile) ? $Account[0]->ImageProfile : "",
                                    "Post"        => $Post,
                                    "Follower"    => $Follower,
                                    "Following"   => $Following));

        JSON(["Message" => 1000, "Result" => $Result]);
    }

    function GetProfileEdit($App)
    {
        $Position = "";
        $Account = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($App->Auth->ID)])->toArray();

        if (isset($Account[0]->Lat) && isset($Account[0]->Lon))
            $Position = $Account[0]->Lat . ":" . $Account[0]->Lon;

        $Result = json_encode(array("Username"    => isset($Account[0]->Username) ? $Account[0]->Username : "",
                                    "Description" => isset($Account[0]->Description) ? $Account[0]->Description : "",
                                    "Link"        => isset($Account[0]->Link) ? $Account[0]->Link : "",
                                    "Name"        => isset($Account[0]->Name) ? $Account[0]->Name : "",
                                    "Position"    => $Position,
                                    "Location"    => isset($Account[0]->Location) ? $Account[0]->Location : "",
                                    "Email"       => isset($Account[0]->Email) ? $Account[0]->Email : "",
                                    "BackGround"  => isset($Account[0]->ImageBackGround) ? $Account[0]->ImageBackGround : "",
                                    "Profile"     => isset($Account[0]->ImageProfile) ? $Account[0]->ImageProfile : ""));

        JSON(["Message" => 1000, "Result" => $Result]);
    }

    function SetProfileEdit($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : "";
        $Name = isset($_POST["Name"]) ? $_POST["Name"] : "";
        $Description = isset($_POST["Description"]) ? $_POST["Description"] : "";
        $Link = isset($_POST["Link"]) ? strtolower($_POST["Link"]) : "";
        $Location = isset($_POST["Location"]) ? $_POST["Location"] : "";
        $Email = isset($_POST["Email"]) ? $_POST["Email"] : "";

        $Lat = "";
        $Lon = "";

        if (isset($_POST["Position"]))
        {
            $Pos = explode(":", $_POST["Position"]);
            $Lat = $Pos[0];
            $Lon = $Pos[1];
        }

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (strlen($Username) <= 2)
            JSON(["Message" => 2]);

        if (strlen($Username) >= 33)
            JSON(["Message" => 3]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 4]);

        if (!isset($Email) || empty($Email))
            JSON(["Message" => 5]);

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            JSON(["Message" => 6]);

        if (strlen($Email) >= 65)
            JSON(["Message" => 7]);

        if (strlen($Name) <= 2)
            JSON(["Message" => 8]);

        if (strlen($Name) >= 33)
            JSON(["Message" => 9]);

        if (strlen($Description) > 150)
            JSON(["Message" => 10]);

        $ImageProfile = "";
        $ImageBackGround = "";
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Account = $App->DB->Find('account', ['_id' => $OwnerID])->toArray();

        if (isset($_FILES['ImageProfile']))
        {
            $FileName = $_FILES['ImageProfile']['name'];
            $FileSize = $_FILES['ImageProfile']['size'];
            $FileTemp = $_FILES['ImageProfile']['tmp_name'];
            $FileType = $_FILES['ImageProfile']['type'];

            if (!in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("jpeg", "jpg")))
                break;

            if ($FileType != "image/jpeg")
                break;

            if ($FileSize > 2097152)
                break;

            Upload::DeleteFile($Account[0]->ImageProfile);
            $Server = Upload::GetBestServer();

            $Channel = curl_init();
            curl_setopt($Channel, CURLOPT_URL, $Server);
            curl_setopt($Channel, CURLOPT_HEADER, false);
            curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $OwnerID, "FILE" => new CurlFile($FileTemp, $FileType)]);
            $URL = curl_exec($Channel);
            curl_close($Channel);

            $ImageProfile = $Server . $URL;
        }
        
        if (isset($_FILES['ImageBackGround']))
        {
            $FileName = $_FILES['ImageBackGround']['name'];
            $FileSize = $_FILES['ImageBackGround']['size'];
            $FileTemp = $_FILES['ImageBackGround']['tmp_name'];
            $FileType = $_FILES['ImageBackGround']['type'];

            if (!in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("jpeg", "jpg")))
                break;

            if ($FileType != "image/jpeg")
                break;

            if ($FileSize > 2097152)
                break;

            Upload::DeleteFile($Account[0]->ImageBackGround);
            $Server = Upload::GetBestServer();

            $Channel = curl_init();
            curl_setopt($Channel, CURLOPT_URL, $Server);
            curl_setopt($Channel, CURLOPT_HEADER, false);
            curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $OwnerID, "FILE" => new CurlFile($FileTemp, $FileType)]);
            $URL = curl_exec($Channel);
            curl_close($Channel);

            $ImageBackGround = $Server . $URL;
        }

        if ($ImageProfile == "" && isset($Account[0]->ImageProfile))
            $ImageProfile = $Account[0]->ImageProfile;

        if ($ImageBackGround == "" && isset($Account[0]->ImageBackGround))
            $ImageBackGround = $Account[0]->ImageBackGround;

        $App->DB->Update('account', ['_id' => $OwnerID], ['$set' => ['Username'        => $Username,
                                                                     'Description'     => $Description,
                                                                     'Link'            => $Link,
                                                                     'Name'            => $Name,
                                                                     'Email'           => $Email,
                                                                     'Lat'             => $Lat,
                                                                     'Lon'             => $Lon,
                                                                     'Location'        => $Location,
                                                                     'ImageProfile'    => $ImageProfile,
                                                                     'ImageBackGround' => $ImageBackGround]]);

        JSON(["Message" => 1000]);
    }

    function RemoveProfileImage($App)
    {
        $ID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $Account = $App->DB->Find('account', ['_id' => $ID])->toArray();

        Upload::DeleteFile($Account[0]->ImageProfile);

        $App->DB->Update('account', ['_id' => $ID], ['$set' => ['ImageProfile' => ""]]);

        JSON(["Message" => 1000]);
    }

    function RemoveProfileBackGround($App)
    {
        $ID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $Account = $App->DB->Find('account', ['_id' => $ID])->toArray();

        Upload::DeleteFile($Account[0]->ImageBackGround);

        $App->DB->Update('account', ['_id' => $ID], ['$set' => ['ImageBackGround' => ""]]);

        JSON(["Message" => 1000]);
    }
?>