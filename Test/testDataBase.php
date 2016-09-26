<?php

    // Root Path And Key Access
    define("ROOT",   dirname(__FILE__) . DIRECTORY_SEPARATOR . '../', true);

    // Auto Loader
    include_once(ROOT . "System/AutoLoader.php");

    $DB = new DataBase();


    // delete all records from previous test
    $DB->delete('test',['name' => 'name1']);
    $DB->delete('test',['name' => 'updated_name2']);
    $DB->delete('test',['name' => 'name2']);
    $DB->delete('test',['name' => 'name3']);

    // insert some data
    $DB->insert('test', ['name' => 'name1']);
    $DB->insert('test', ['name' => 'name2']);
    $DB->insert('test', ['name' => 'name3']);


    $DB->update('test', ['name' => 'name2'], ['name' => 'updated_name2']);


    $name1 = $DB->find('test', ['name' => 'name1']);
    echo "find result:\n";
    var_dump($name1->toArray());


    $data = $DB->all('test');
    echo "all data: \n" ;
    foreach ($data as $d) {
        echo $d->name . "\n";
    }