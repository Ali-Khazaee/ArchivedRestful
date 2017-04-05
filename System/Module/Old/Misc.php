<?php
    if (!defined("ROOT")) { exit(); }

    function MiscGetProfileImage($App)
    {
        $ID = isset($_POST["ID"]) ? strtolower($_POST["ID"]) : NULL;

        if (!isset($ID) || empty($ID))
            JSON(["Message" => 1]);

        $Profile = $App->DB->Find('account', ['ID' => $ID])->toArray();

        JSON(["Message" => 1000, "URL" => $URL]);
    }
?>