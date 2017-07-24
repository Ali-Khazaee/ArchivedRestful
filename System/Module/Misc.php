<?php
    if (!defined("ROOT")) { exit(); }

    function LastOnline($App)
    {
        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($App->Auth->ID)], ['$set' => ['LastOnline' => time()]]);
    }

    function NotificationList($App)
    {
        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $NotificationList = $App->DB->Find('notification', [], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($NotificationList as $Notification)
        {
            $Account = $App->DB->Find('account', ['_id' => $Notification->SenderID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            array_push($Result, array("Username" => $Account[0]->Username,
                                      "Avatar"   => isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : "",
                                      "Type"     => $Notification->Type,
                                      "Time"     => $Notification->Time));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function NotificationService($App)
    {
        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $NotificationList = $App->DB->Find('notification', ["Seen" => 0], ['limit' => 5, 'sort' => ['Time' => 1]])->toArray();

        foreach ($NotificationList as $Notification)
        {
            $App->DB->Update('notification', ["_id" => $Notification->_id], ['$set' => ['Seen' => 1]]);

            $Account = $App->DB->Find('account', ['_id' => $Notification->SenderID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            array_push($Result, array("Username" => $Account[0]->Username,
                                      "Avatar"   => isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : "",
                                      "Type"     => $Notification->Type,
                                      "Time"     => $Notification->Time));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }
?>