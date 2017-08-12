<?php
    if (!defined("ROOT")) { exit(); }

    function Notification($App)
    {
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Account = $App->DB->Find('account', ['_id' => $OwnerID], ["projection" => ["_id" => 0, "Notification" => 1]])->toArray();

        if (isset($Account[0]->Notification) && $Account[0]->Notification)
            $Notification = false;
        else
            $Notification = true;

        $App->DB->Update('account', ['_id' => $OwnerID], ['$set' => ['Notification' => $Notification]]);

        JSON(["Message" => 1000, "Notification" => $Notification]);
    }

    function NotificationList($App)
    {
        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $NotificationList = $App->DB->Find('notification', ["OwnerID" => $OwnerID], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

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

        $Account = $App->DB->Find('account', ['_id' => $OwnerID], ["projection" => ["_id" => 0, "Notification" => 1]])->toArray();

        if (isset($Account[0]->Notification) && $Account[0]->Notification)
            $Notification = true;
        else
            $Notification = false;

        JSON(["Message" => 1000, "Result" => json_encode($Result), "Notification" => $Notification]);
    }

    function NotificationService($App)
    {
        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $NotificationList = $App->DB->Find('notification', ["OwnerID" => $OwnerID, "Seen" => 0])->toArray();

        foreach ($NotificationList as $Notification)
        {
            $App->DB->Update('notification', ["_id" => $Notification->_id], ['$set' => ['Seen' => 1]]);

            $Account = $App->DB->Find('account', ['_id' => $Notification->SenderID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            array_push($Result, array("Username" => $Account[0]->Username, "Type" => $Notification->Type));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }
?>