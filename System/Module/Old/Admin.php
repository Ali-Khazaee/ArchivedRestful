<?php
    if (!defined("ROOT")) { exit(); }

    function AdminGetTotalOnline($App)
    {
        $Time = isset($_POST["Time"]) ? strtolower($_POST["Time"]) : 86400;
        $Time = time() - $Time;

        $Online = $App->DB->Command(["count" => "account", "query" => ['LastOnline' => ['$gt' => (int) $Time]]])->toArray()[0]->n;

        JSON(["Message" => 1000, "Count" => $Online]);
    }
?>