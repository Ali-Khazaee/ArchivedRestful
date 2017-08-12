<?php
    if (!defined("ROOT")) { exit(); }

    function LastOnline($App)
    {
        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($App->Auth->ID)], ['$set' => ['LastOnline' => time()]]);
    }
?>