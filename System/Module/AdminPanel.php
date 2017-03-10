<?php
    if (!defined("ROOT")) { exit(); }

    function AdminGetTotalOnline($App)
    {
        $Time = isset($_POST["Time"]) ? strtolower($_POST["Time"]) : 86400;
        $Time = time() - $Time;

        $Online = $App->DB->Find('account', ['LastOnline' => ['$gt' => (int) $Time]])->toArray();
        
        // $Online2 = $App->DB->Command('account', ['count' => 'LastOnline', 'query' => ['$gt' => (int) $Time]])->toArray();

        JSON(["Message" => 1000, "Count" => count($Online)]);
    }
?>