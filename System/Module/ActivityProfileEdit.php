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
                        "BackGround"  => isset($User[0]->BackGround) ? $User[0]->BackGround : "",
                        "Profile"     => isset($User[0]->Profile) ? $User[0]->Profile : "");

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function ActivityProfileEditSave($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : NULL;
        $Description = isset($_POST["Description"]) ? $_POST["Description"] : NULL;
        $Link = isset($_POST["Link"]) ? strtolower($_POST["Link"]) : NULL;
        $Email = isset($_POST["Email"]) ? $_POST["Email"] : NULL;

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

        $FileName = $_FILES['ImageProfile']['name'];
        $FileSize = $_FILES['ImageProfile']['size'];
        $FileTemp = $_FILES['ImageProfile']['tmp_name'];
        $FileType = $_FILES['ImageProfile']['type'];

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

    function ActivityProfileEditDeleteProfile($App)
    {
        
    }

    function ActivityProfileEditDeleteBackGround($App)
    {
        
    }
?>