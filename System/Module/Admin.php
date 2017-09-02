<?php
    if (!defined("ROOT")) { exit(); }

    function AdminStatus($App)
    {
        $Password = isset($_POST["Password"]) ? $_POST["Password"] : "";

        if (ADMIN_PASSWORD != $Password)
            JSON(["Message" => 50]);

        $Account = $App->DB->Command(["count" => "account", "query" => []])->toArray()[0]->n;
        $Notification = $App->DB->Command(["count" => "notification", "query" => []])->toArray()[0]->n;
        $Post = $App->DB->Command(["count" => "post", "query" => []])->toArray()[0]->n;
        $Bookmark = $App->DB->Command(["count" => "post_bookmark", "query" => []])->toArray()[0]->n;
        $Comment = $App->DB->Command(["count" => "post_comment", "query" => []])->toArray()[0]->n;
        $CommentLike = $App->DB->Command(["count" => "post_comment_like", "query" => []])->toArray()[0]->n;
        $PostLike = $App->DB->Command(["count" => "post_like", "query" => []])->toArray()[0]->n;
        $Report = $App->DB->Command(["count" => "report", "query" => []])->toArray()[0]->n;
        $Tag = $App->DB->Command(["count" => "tag", "query" => []])->toArray()[0]->n;

        JSON(["Message" => 1000, "Account" => $Account, "Notification" => $Notification, "Post" => $Post, "Bookmark" => $Bookmark, "Comment" => $Comment, "CommentLike" => $CommentLike, "PostLike" => $PostLike, "Report" => $Report, "Tag" => $Tag]);
    }
?>