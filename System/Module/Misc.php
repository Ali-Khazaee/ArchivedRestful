<?php
    if (!defined("ROOT")) { exit(); }

    function CategoryList($App)
    {
        $CategoryList = $App->DB->Find('category', [], ['sort' => [ '_id' => 1]])->toArray();

        $Cate = array();

        foreach ($CategoryList as $Category)
           array_push($Cate, array("ID" => $Category->_id, "Name" => $Category->Name, "Encode" => $Category->Encode));

        JSON(["Status" => "Success", "Message" => Lang("SUCCESS"), "Data" => $Cate]);
    }

    function CategorySave($App)
    {
        $CatList = $_POST["CatList"];

        if (!isset($CatList) || empty($CatList))
            JSON(["Status" => "Failed", "Message" => Lang("CATEGORYSAVE_EMPTY")]);

        $Cat = array_unique(explode(".", $CatList), SORT_REGULAR);
        $Cat = array_values($Cat);

        if (count($Cat) <= 4)
            JSON(["Status" => "Failed", "Message" => Lang("CATEGORYSAVE_NOT_ENOUGH")]);

        $CategoryList = $App->DB->Find('category_storage', ["_id" => new MongoDB\BSON\ObjectID($App->Auth->ID)])->toArray();

        if (!empty($CategoryList))
            JSON(["Status" => "Failed", "Message" => Lang("CATEGORYSAVE_ALREADY")]);

        for ($I = 0; $I < count($Cat); ++$I)
            $App->DB->Insert('category_storage', ['ID' => new MongoDB\BSON\ObjectID($App->Auth->ID), 'CategoryID' => $Cat[$I]]);

        JSON(["Status" => "Success", "Message" => Lang("SUCCESS")]);
    }
?>