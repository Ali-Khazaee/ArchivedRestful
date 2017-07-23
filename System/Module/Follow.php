<?php
    if (!defined("ROOT")) { exit(); }

    function Follow($App)
    {
        if (!isset($_POST["Username"]) || !preg_match("/^(?![^a-z])(?!.*\.\.)[a-z0-9_.]+(?<![^a-z])$/", $_POST["Username"]))
            JSON(["Message" => 1]);

        $Account = $App->DB->Find('account', ['Username' => $_POST["Username"]], ["projection" => ["_id" => 1]])->toArray();

        if (empty($Account))
            JSON(["Message" => 2]);

        if ($Account[0]->_id == $App->Auth->ID)
            JSON(["Message" => 3]);

        $FollowID = new MongoDB\BSON\ObjectID($Account[0]->_id);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Query = ['$and' => [["OwnerID" => $OwnerID, "Follower" => $FollowID]]];

        if (isset($App->DB->Find('follow', $Query, ["projection" => ["_id" => 1]])->toArray()[0]))
        {
            $IsFollow = false;
            $App->DB->Remove('follow', $Query);

            if ($FollowID != $OwnerID)
                $App->DB->Remove('notification', ["OwnerID" => $FollowID, "SenderID" => $OwnerID, "Type" => 3]);
        }
        else
        {
            $IsFollow = true;
            $App->DB->Insert('follow', ["OwnerID" => $OwnerID, "Follower" => $FollowID, "Time" => time()]);

            if ($FollowID != $OwnerID)
                $App->DB->Insert('notification', ["OwnerID" => $FollowID, "SenderID" => $OwnerID, "Type" => 3, "Seen" => 0, "Time" => time()]);
        }

        JSON(["Message" => 1000, "Follow" => $IsFollow]);
    }

    function FollowingGet($App)
    {
        if (!isset($_POST["Username"]) || !preg_match("/^(?![^a-z])(?!.*\.\.)[a-z0-9_.]+(?<![^a-z])$/", $_POST["Username"]))
            JSON(["Message" => 1]);

        $Account = $App->DB->Find('account', ['Username' => $_POST["Username"]], ["projection" => ["_id" => 1]])->toArray();

        if (empty($Account))
            JSON(["Message" => 2]);

        $Result = array();
        $FollowID = new MongoDB\BSON\ObjectID($Account[0]->_id);
        $FollowingList = $App->DB->Find('follow', ['OwnerID' => $FollowID], ["projection" => ["_id" => 0, "Follower" => 1, "Time" => 1], 'skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($FollowingList as $Follow)
        {
            $Account = $App->DB->Find('account', ['_id' => $Follow->Follower], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            array_push($Result, array("Username" => $Account[0]->Username, "Avatar" => (isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : ""), "Time" => $Follow->Time));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function FollowersGet($App)
    {
        if (!isset($_POST["Username"]) || !preg_match("/^(?![^a-z])(?!.*\.\.)[a-z0-9_.]+(?<![^a-z])$/", $_POST["Username"]))
            JSON(["Message" => 1]);

        $Account = $App->DB->Find('account', ['Username' => $_POST["Username"]], ["projection" => ["_id" => 1]])->toArray();

        if (empty($Account))
            JSON(["Message" => 2]);

        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $FollowID = new MongoDB\BSON\ObjectID($Account[0]->_id);
        $FollowersList = $App->DB->Find('follow', ['Follower' => $FollowID], ["projection" => ["_id" => 0, "OwnerID" => 1, "Time" => 1], 'skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($FollowersList as $Follow)
        {
            $Account = $App->DB->Find('account', ['_id' => $Follow->OwnerID], ["projection" => ["_id" => 1, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            if (isset($App->DB->Find('follow', ['$and' => [["OwnerID" => $OwnerID, "Follower" => $Account[0]->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $IsFollow = true;
            else
                $IsFollow = false;

            array_push($Result, array("Username" => $Account[0]->Username, "Avatar" => (isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : ""), "Time" => $Follow->Time, "Follow" => $IsFollow));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }
?>