<?php
    if (!defined("ROOT")) { exit(); }

    function LikeList($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);
        
        $List = array();
        $Skip = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);

        $Likes = $App->DB->Find('like', ['PostID' => $PostID], ['skip' => $Skip, 'limit' => 10, 'sort' => ['Time' => -1]])->toArray();

        foreach ($Likes as $Like)
        {
            $Username = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($Like->OwnerID)])->toArray();

            if (isset($Username[0]))
                array_push($List, array("OwnerID" => $Like->OwnerID->__toString(), "Username" => $Username[0]->Username, "URL" => (isset($Username[0]->Profile) ? $Username[0]->Profile : ""), "Time" => $Like->Time));
        }

        JSON(["Message" => 1000, "Result" => json_encode($List)]);
    }

    function LikePost($App)
    {
        if (!isset($_POST["PostID"]) || empty($_POST["PostID"]))
            JSON(["Message" => 1]);

        $PostID = new MongoDB\BSON\ObjectID($_POST["PostID"]);
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $Query = ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]];

        $Post = $App->DB->Find('like', $Query)->toArray();

        if (isset($Post[0]))
            $App->DB->Remove('like', $Query, ['limit' => 1]);
        else
            $App->DB->Insert('like', ["OwnerID" => $OwnerID, "PostID" => $PostID, "Time" => time()]);

        JSON(["Message" => 1000]); 
    }
?>