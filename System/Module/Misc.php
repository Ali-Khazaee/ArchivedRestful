<?php
    if (!defined("ROOT")) { exit(); }

    function CategoryList($App)
    {
        $CategoryList = $App->DB->Find('category', [])->toArray();

        $CategoryList = $CategoryList->sort(array("_id" => -1));

        foreach ($CategoryList As $Category)
            echo $Category->_id . "<br>";

        #JSON(["Status" => "Success", "Message" => Lang("SUCCESS")]);
    }
?>