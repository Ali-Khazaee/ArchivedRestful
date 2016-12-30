<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $tokens = $App->DB->Find('log', [])->toArray();

    var_dump($tokens);