<?php
    if (!defined("ROOT")) { exit(); }

    function ActivityProfileEdit($App)
    {
        $ID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        $User = $App->DB->Find('account', ['_id' => $ID])->toArray();

        $Result = array("Username"    => isset($User[0]->Username) ? $User[0]->Username : "",
                        "Description" => isset($User[0]->Description) ? $User[0]->Description : "",
                        "Link"        => isset($User[0]->Link) ? $User[0]->Link : "",
                        "Email"       => isset($User[0]->Email) ? $User[0]->Email : "",
                        "BackGround"  => isset($User[0]->ImageBackGround) ? $User[0]->ImageBackGround : "",
                        "Profile"     => isset($User[0]->ImageProfile) ? $User[0]->ImageProfile : "");

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function ActivityProfileEditSave($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : NULL;
        $Description = isset($_POST["Description"]) ? $_POST["Description"] : NULL;
        $Link = isset($_POST["Link"]) ? strtolower($_POST["Link"]) : NULL;
        $Email = isset($_POST["Email"]) ? $_POST["Email"] : NULL;

        $OldEmail = false;
        $OldUsername = false;

        if (!isset($Username) || empty($Username))
            $OldUsername = true;

        if (strlen($Username) <= 2)
            $OldUsername = true;

        if (strlen($Username) >= 33)
            $OldUsername = true;

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            $OldUsername = true;

        if (!isset($Email) || empty($Email))
            $OldEmail = true;

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            $OldEmail = true;

        if (strlen($Email) >= 65)
            $OldEmail = true;

        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $ImageProfile = NULL;
        $ImageBackGround = NULL;

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

        $Account = $App->DB->Find('account', ['_id' => $OwnerID])->toArray();

        if ($OldUsername && isset($Account[0]->Username))
            $Username = $Account[0]->Username;

        if ($OldEmail)
            $Email = $Account[0]->Email;

        if ($ImageProfile == NULL && isset($Account[0]->ImageProfile))
            $ImageProfile = $Account[0]->ImageProfile;

        if ($ImageBackGround == NULL && isset($Account[0]->ImageBackGround))
            $ImageBackGround = $Account[0]->ImageBackGround;

        $App->DB->Update('account', ['_id' => $OwnerID], ['$set' =>
                                                         ['Username' => $Username,
                                                          'Email' => $Email,
                                                          'ImageProfile' => $ImageProfile,
                                                          'ImageBackGround' => $ImageBackGround,
                                                          'Description' => $Description,
                                                          'Link' => $Link]]);

        JSON(["Message" => 1000]);
    }

    function ActivityProfileEditDeleteProfile($App)
    {
        
    }

    function ActivityProfileEditDeleteBackGround($App)
    {
        
    }
?>