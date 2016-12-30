<?php

    include "app.php";

    // Run The Application
    $App = new Application();


    $tokens = $App->DB->Find('tokens', [])->toArray();

    var_dump($tokens);