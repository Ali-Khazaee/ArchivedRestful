<?php
    if (!defined("ROOT")) { exit(); }

    function SearchPeople($App)
    {
        $Name = isset($_POST["Name"]) ? strtolower($_POST["Name"]) : "";

        if (!isset($Name) || empty($Name))
            JSON(["Message" => 1]);

        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $PeopleList = $App->DB->Find('account', ['Username' => ['$regex' => $Name]], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10])->toArray();

        foreach ($PeopleList as $People)
        {
            if (isset($People->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($People->AvatarServer);
            else
                $AvatarServerURL = "";

            $Follower = $App->DB->Command(["count" => "follow", "query" => ['Follower' => $People->_id]])->toArray()[0]->n;

            if (!isset($Follower) || empty($Follower))
                $Follower = 0;

            array_push($Result, array("Username" => $People->Username,
                                      "Avatar"   => isset($People->Avatar) ? $AvatarServerURL . $People->Avatar : "",
                                      "Follower" => $Follower));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function SearchTag($App)
    {
        $Tag = isset($_POST["Tag"]) ? strtolower($_POST["Tag"]) : "";

        if (!isset($Tag) || empty($Tag))
            JSON(["Message" => 1]);

        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $TagList = $App->DB->Find('tag', ['Tag' => ['$regex' => $Tag]], ['limit' => 15])->toArray();

        foreach ($TagList as $Tag)
        {
            $Count = $App->DB->Command(["count" => "post", "query" => ['Message' => ['$regex' => ("#" . strtolower($Tag->Tag)), '$options' => "i"]]])->toArray()[0]->n;

            if (!isset($Count) || empty($Count))
                $Count = 0;

            array_push($Result, array("Tag" => $Tag->Tag, "Count" => $Count));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }

    function SearchTagList($App)
    {
        $Tag = isset($_POST["Tag"]) ? strtolower($_POST["Tag"]) : "";

        if (!isset($Tag) || empty($Tag))
            JSON(["Message" => 1]);

        $Result = array();
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);
        $PostList = $App->DB->Find('post', ['Message' => ['$regex' => ("#" . $Tag)]], ['skip' => (isset($_POST["Skip"]) ? $_POST["Skip"] : 0), 'limit' => 10])->toArray();

        foreach ($PostList as $Post)
        {
            $Account = $App->DB->Find('account', ['_id' => $Post->OwnerID], ["projection" => ["_id" => 0, "Username" => 1, "AvatarServer" => 1, "Avatar" => 1]])->toArray();

            if (isset($App->DB->Find('post_like', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Like = true;
            else
                $Like = false;

            if (isset($App->DB->Find('post_bookmark', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Post->_id]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $BookMark = true;
            else
                $BookMark = false;

            $LikeCount = $App->DB->Command(["count" => "post_like", "query" => ['PostID' => $Post->_id]])->toArray()[0]->n;

            if (!isset($LikeCount) || empty($LikeCount))
                $LikeCount = 0;

            $CommentCount = $App->DB->Command(["count" => "post_comment", "query" => ['PostID' => $Post->_id]])->toArray()[0]->n;

            if (!isset($CommentCount) || empty($CommentCount))
                $CommentCount = 0;

            if (isset($Account[0]->AvatarServer))
                $AvatarServerURL = Upload::GetServerURL($Account[0]->AvatarServer);
            else
                $AvatarServerURL = "";

            $PostData = array();

            if ($Post->Type == 1 || $Post->Type == 2)
            {
                if (isset($Post->DataServer))
                    $DataServerURL = Upload::GetServerURL($Post->DataServer);
                else
                    $DataServerURL = "";

                foreach ($Post->Data As $Data)
                    array_push($PostData, $DataServerURL . $Data);
            }
            elseif ($Post->Type == 3)
            {
                $PostData = $Post->Data;
            }

            if (isset($App->DB->Find('follow', ['$and' => [["OwnerID" => $OwnerID, "Follower" => $Post->OwnerID]]], ["projection" => ["_id" => 1]])->toArray()[0]))
                $Follow = true;
            else
                $Follow = false;

            array_push($Result, array("PostID"       => $Post->_id->__toString(),
                                      "OwnerID"      => $Post->OwnerID->__toString(),
                                      "Type"         => $Post->Type,
                                      "Category"     => $Post->Category,
                                      "Time"         => $Post->Time,
                                      "Comment"      => $Post->Comment,
                                      "Message"      => isset($Post->Message) ? $Post->Message : "",
                                      "Data"         => $PostData,
                                      "Username"     => $Account[0]->Username,
                                      "Avatar"       => isset($Account[0]->Avatar) ? $AvatarServerURL . $Account[0]->Avatar : "",
                                      "Like"         => $Like,
                                      "LikeCount"    => $LikeCount,
                                      "CommentCount" => $CommentCount,
                                      "BookMark"     => $BookMark,
                                      "Follow"       => $Follow));
        }

        JSON(["Message" => 1000, "Result" => json_encode($Result)]);
    }
?>