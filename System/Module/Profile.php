<?php
    if (!defined("ROOT")) { exit(); }

    function ProfileGet($App)
    {
        $Result = $App->DB->Find('account', ["_id" => new MongoDB\BSON\ObjectID($App->Auth->ID)])->toArray();
        JSON(["Status" => "Success", "Message" => Lang("SUCCESS"), "Data" => ["Name" => $Result[0]->Name, "Bio" => $Result[0]->Bio, "Web" => $Result[0]->Web]]);
    }

    function ProfileSet($App)
    {
        $Name = isset($_POST["Name"]) ? $_POST["Name"] : "";
        $Bio  = isset($_POST["Bio"]) ? $_POST["Bio"] : "";
        $Web  = isset($_POST["Web"]) ? $_POST["Web"] : "";

        if (strlen($Name) <= 2)
            JSON(["Status" => "Failed", "Message" => Lang("PROFILESET_USERNAME_SHORT")]);

        if (strlen($Name) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("PROFILESET_USERNAME_LONG")]);

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($App->Auth->ID)], ['$push' => ['Name' => $Name, 'Bio' => $Bio, 'Web' => $Web]]);
        JSON(["Status" => "Success", "Message" => Lang("SUCCESS")]);
    }
?>