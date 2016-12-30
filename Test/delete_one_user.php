<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $App->DB->Delete('account', ['_id' => new MongoDB\BSON\ObjectID( '57edad6c9a89200ca86d7671' )]);

