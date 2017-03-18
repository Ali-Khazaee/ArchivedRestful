<?php
    if (!defined("ROOT")) { exit(); }

    function GeneralAdapterPostMoment($App)
    {
        $PostData = array();
        $PostTime = isset($_POST["PostTime"]) ? $_POST["PostTime"] : 0;
        $SkipCount = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        if ($PostTime)
            $PostList = $App->DB->Find('post_moment', ['Time' => ['$gt' => (int) $PostTime]], ['skip' => $SkipCount, 'limit' => 5, 'sort' => ['Time' => -1]])->toArray();
        else
            $PostList = $App->DB->Find('post_moment', [], ['skip' => $SkipCount, 'limit' => 5, 'sort' => ['Time' => -1]])->toArray();

        foreach ($PostList as $Post)
        {
            $Username = $App->DB->Find('account', ['_id' => $OwnerID])->toArray();

            if (isset($Username[0]))
            {
                $PostID = new MongoDB\BSON\ObjectID($Post->_id->__toString());

                $Like = $App->DB->Find('post_moment_like', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]])->toArray();

                if (isset($Like[0]))
                    $Like = true;
                else
                    $Like = false;

                $Bookmark = $App->DB->Find('post_bookmark', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $PostID]]])->toArray();

                if (isset($Bookmark[0]))
                    $Bookmark = true;
                else
                    $Bookmark = false;

                $LikeCount = $App->DB->Find('post_moment_like', ["PostID" => $PostID])->toArray();

                if (isset($LikeCount[0]))
                    $LikeCount = count($LikeCount);
                else
                    $LikeCount = 0;

                $CommentCount = $App->DB->Find('post_moment_comment', ["PostID" => $PostID])->toArray();

                if (isset($CommentCount[0]))
                    $CommentCount = count($CommentCount);
                else
                    $CommentCount = 0;

                array_push($PostData, array("PostID"       => $Post->_id->__toString(),
                                            "OwnerID"      => $Post->OwnerID->__toString(),
                                            "Username"     => $Username[0]->Username,
                                            "Time"         => $Post->Time,
                                            "Message"      => $Post->Message,
                                            "Data"         => $Post->Data,
                                            "Type"         => $Post->Type,
                                            "Comment"      => $Post->Comment,
                                            "CommentCount" => $CommentCount,
                                            "Like"         => $Like,
                                            "LikeCount"    => $LikeCount,
                                            "Bookmark"     => $Bookmark));
            }
        }

        JSON(["Message" => 1000, "Result" => json_encode($PostData)]);
    }
?>