<?php
    if (!defined("ROOT")) { exit(); }

    function MomentWrite($App)
    {
        $Message  = isset($_POST["Message"])  ? $_POST["Message"]  : NULL;
        $Category = isset($_POST["Category"]) ? $_POST["Category"] : NULL;
        $Type     = isset($_POST["Type"])     ? $_POST["Type"]     : NULL;
        $Link     = isset($_POST["Link"])     ? $_POST["Link"]     : NULL;

        if ($Type == 0 && $Message <= 19)
            JSON(["Message" => 1]);

        if (strlen($Message) >= 150)
            $Message = substr($Message, 0, 150);

        $Data = array();

        if ($Type == 1)
        {
            $ImageCount = 0;

            foreach ($_FILES AS $File)
            {
                if ($ImageCount > 2)
                    continue;

                $FileName = $File['name'];
                $FileSize = $File['size'];
                $FileTemp = $File['tmp_name'];
                $FileType = $File['type'];

                if (!in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("jpeg", "jpg")))
                    continue;

                if ($FileType != "image/jpeg")
                    continue;

                if ($FileSize > 2097152)
                    continue;

                $ImageCount++;
                $Server = Upload::GetBestServer();

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_IMAGE", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $App->Auth->ID, "FILE" => new CurlFile($FileTemp, $FileType)]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                array_push($Data, ($Server . $URL));
            }
        }
        elseif ($Type == 2)
        {
            $VideoCount = 0;

            foreach ($_FILES AS $File)
            {
                if ($VideoCount > 0)
                    continue;

                $FileName = $File['name'];
                $FileSize = $File['size'];
                $FileTemp = $File['tmp_name'];
                $FileType = $File['type'];

                if (!in_array(strtolower(pathinfo($FileName, PATHINFO_EXTENSION)), array("mp4")))
                    continue;

                if ($FileType != "video/mp4")
                    continue;

                if ($FileSize > 5242880)
                    continue;

                $VideoCount++;
                $Server = Upload::GetBestServer();

                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_VIDEO", "TOKEN" => Upload::GetServerToken($Server), "FOLDER" => $App->Auth->ID, "FILE" => new CurlFile($FileTemp, $FileType)]);
                $URL = curl_exec($Channel);
                curl_close($Channel);

                array_push($Data, ($Server . $URL));
            }
        }
        elseif ($Type == 3)
        {
            array_push($Data, $Link);
        }

        if ($Category == NULL || $Category > 17 || $Category < 0)
            $Category = 0;

        $App->DB->Insert('post', ['OwnerID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'Type' => $Type, 'Data' => $Data, 'Message' => $Message, 'Category' => $Category, 'Time' => time()]);
    }

    function MomentList($App)
    {
        $Moment  = array();
        $Time    = isset($_POST["Time"]) ? $_POST["Time"] : 0;
        $Skip    = isset($_POST["Skip"]) ? $_POST["Skip"] : 0;
        $OwnerID = new MongoDB\BSON\ObjectID($App->Auth->ID);

        if ($Time)
            $MomentList = $App->DB->Find('post', ['Time' => ['$gt' => (int) $Time]], ['skip' => $Skip, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();
        else
            $MomentList = $App->DB->Find('post', [], ['skip' => $Skip, 'limit' => 8, 'sort' => ['Time' => -1]])->toArray();

        foreach ($MomentList as $Mom)
        {
            $Username = $App->DB->Find('account', ['_id' => $OwnerID])->toArray();

            if (isset($Username[0]))
            {
                $Like = $App->DB->Find('like', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Mom->_id]]])->toArray();

                if (isset($Like[0]))
                    $Like = true;
                else
                    $Like = false;

                $BookMark = $App->DB->Find('bookmark', ['$and' => [["OwnerID" => $OwnerID, "PostID" => $Mom->_id]]])->toArray();

                if (isset($BookMark[0]))
                    $BookMark = true;
                else
                    $BookMark = false;

                $LikeCount = $App->DB->Find('like', ["PostID" => $Mom->_id])->toArray();

                if (isset($LikeCount[0]))
                    $LikeCount = count($LikeCount);
                else
                    $LikeCount = 0;

                $CommentCount = $App->DB->Find('comment', ["PostID" => $Mom->_id])->toArray();

                if (isset($CommentCount[0]))
                    $CommentCount = count($CommentCount);
                else
                    $CommentCount = 0;

                array_push($Moment, array("PostID"       => $Mom->_id->__toString(),
                                          "OwnerID"      => $Mom->OwnerID->__toString(),
                                          "Username"     => $Username[0]->Username,
                                          "Time"         => $Mom->Time,
                                          "Message"      => isset($Mom->Message) ? $Mom->Message : "",
                                          "Data"         => isset($Mom->Data) ? $Mom->Data : "",
                                          "Type"         => isset($Mom->Type) ? $Mom->Type : 0,
                                          "Comment"      => isset($Mom->Comment) ? $Mom->Comment : false,
                                          "CommentCount" => $CommentCount,
                                          "Like"         => $Like,
                                          "LikeCount"    => $LikeCount,
                                          "BookMark"     => $BookMark));
            }
        }

        JSON(["Message" => 1000, "Result" => json_encode($Moment)]);
    }
?>