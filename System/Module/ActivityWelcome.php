<?php
    if (!defined("ROOT")) { exit(); }

    function ActivityWelcomeUsernameFree($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : NULL;

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (strlen($Username) <= 2)
            JSON(["Message" => 2]);

        if (strlen($Username) >= 33)
            JSON(["Message" => 3]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 4]);

        if (empty($App->DB->Find('account', ['Username' => $Username])->toArray()))
            JSON(["Message" => 1000]);

        JSON(["Message" => 999]);
    }

    function ActivityWelcomeEmailSign($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]);
        $Password = isset($_POST["Password"]) ? $_POST["Password"];
        $Email    = isset($_POST["Email"]) ? strtolower($_POST["Email"]);
        $Session  = isset($_POST["Session"]) ? strtolower($_POST["Session"]);

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (!isset($Password) || empty($Password))
            JSON(["Message" => 2]);

        if (!isset($Email) || empty($Email))
            JSON(["Message" => 3]);

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            JSON(["Message" => 4]);

        if (strlen($Username) <= 2)
            JSON(["Message" => 5]);

        if (strlen($Username) >= 33)
            JSON(["Message" => 6]);

        if (strlen($Password) <= 4)
            JSON(["Message" => 7]);

        if (strlen($Password) >= 33)
            JSON(["Message" => 8]);

        if (strlen($Email) >= 65)
            JSON(["Message" => 9]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 10]);

        $App->RateLimit->Call('ActivityWelcomeEmailSignQuery.1.2000');

        if (!empty($App->DB->Find('account', ['Username' => $Username])->toArray()))
            JSON(["Message" => 11]);

        if (!empty($App->DB->Find('account', ['Email' => $Email])->toArray()))
            JSON(["Message" => 12]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $App->RateLimit->Call('ActivityWelcomeEmailSignQueryCreated.1.60000');

        $ID = $App->DB->Insert('account', ['Username' => $Username, 'Password' => password_hash($Password, PASSWORD_BCRYPT), 'Email' => $Email, 'CreatedTime' => time()])->__toString();

        $Token = $App->Auth->CreateToken(["ID" => $ID]);

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

        JSON(["Message" => 1000, "Token" => $Token, "AccountID" => $ID]);
    }

    function ActivityWelcomeSignIn($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : NULL;
        $Password = isset($_POST["Password"]) ? $_POST["Password"] : NULL;
        $Session = isset($_POST["Session"]) ? $_POST["Session"] : NULL;

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (!isset($Password) || empty($Password))
            JSON(["Message" => 2]);

        if (strlen($Username) <= 2)
            JSON(["Message" => 3]);

        if (strlen($Username) >= 33)
            JSON(["Message" => 4]);

        if (strlen($Password) <= 4)
            JSON(["Message" => 5]);

        if (strlen($Password) >= 33)
            JSON(["Message" => 6]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 7]);

        $App->RateLimit->Call('ActivityWelcomeSignInQuery.1.2000');

        $Account = $App->DB->Find('account', ['Username' => $Username])->toArray();

        if (empty($Account))
            JSON(["Message" => 8]);

        if (!password_verify($Password, $Account[0]->Password))
            JSON(["Message" => 9]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $ID = $Account[0]->_id->__toString();
        $Token = $App->Auth->CreateToken(["ID" => $ID]);

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

        JSON(["Message" => 1000, "Token" => $Token, "AccountID" => $ID]);
    }

    function ActivityWelcomeReset($App)
    {
        $EmailOrUsername = isset($_POST["EmailOrUsername"]) ? strtolower($_POST["EmailOrUsername"]) : NULL;

        if (!isset($EmailOrUsername) || empty($EmailOrUsername))
            JSON(["Message" => 1]);

        if (strlen($EmailOrUsername) <= 2)
            JSON(["Message" => 2]);

        if (strlen($EmailOrUsername) >= 65)
            JSON(["Message" => 3]);

        if (!filter_var($EmailOrUsername, FILTER_VALIDATE_EMAIL))
            if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
                JSON(["Message" => 4]);

        $App->RateLimit->Call('ActivityWelcomeReset.1.2000');

        $RandomString = '';
        $Characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $CharactersLength = strlen($Characters);

        for ($I = 0; $I < 15; $I++)
            $RandomString .= $Characters[rand(0, $CharactersLength - 1)];

        if (filter_var($EmailOrUsername, FILTER_VALIDATE_EMAIL))
        {
            $Account = $App->DB->Find('account', ['Email' => $EmailOrUsername])->toArray();

            if (empty($Account))
                JSON(["Message" => 8]);

            $Email = $Account[0]->Email;
            $Username = str_rot13(strrev($Account[0]->Username);
        }
        else
        {
            $Account = $App->DB->Find('account', ['Username' => $EmailOrUsername])->toArray();

            if (empty($Account))
                JSON(["Message" => 8]);

            $Email = $Account[0]->Email;
            $Username = str_rot13(strrev($Account[0]->Username);
        }

        JSON(["Message" => 1000]);
    }

    function ActivityWelcomeSignInGoogle($App)
    {
        $Token = isset($_POST["Token"]) ? $_POST["Token"] : NULL;
        $Session = isset($_POST["Session"]) ? $_POST["Session"] : NULL;

        if (!isset($Token) || empty($Token))
            JSON(["Message" => 1]);

        require_once(ROOT. 'System/Library/GoogleAPI/vendor/autoload.php');

        $Client = new Google_Client();
        $PayLoad = $Client->verifyIdToken($Token);

        if (!$PayLoad)
            JSON(["Message" => 2]);

        if ($PayLoad['iss'] != 'accounts.google.com' && $PayLoad['iss'] != 'https://accounts.google.com'):
            JSON(["Message" => 3]);

        if ($PayLoad['aud'] != '590625045379-9pgbc6r8v0794rij59jj50o1gp6ijnvl.apps.googleusercontent.com'):
            JSON(["Message" => 4]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $GoogleID = $PayLoad['sub'];
        $App->RateLimit->Call('ActivityWelcomeSignInGoogleQuery.1.2000');
        $Account = $App->DB->Find('account', ['GoogleID' => $GoogleID])->toArray();

        if (empty($Account))
        {
            $App->RateLimit->Call('ActivityWelcomeSignInGoogleCreated.1.60000');

            $ID = $App->DB->Insert('account', ['GoogleID' => $GoogleID, 'Email' => $PayLoad['email'], 'CreatedTime' => time()])->__toString();

            $Token = $App->Auth->CreateToken(["ID" => $ID]);

            $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

            JSON(["Message" => 1000, "Token" => $Token, "AccountID" => $ID]);
        }
        else
        {
            $ID = $Account[0]->_id->__toString();

            $Token = $App->Auth->CreateToken(["ID" => $ID]);

            $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

            JSON(["Message" => 1000, "Token" => $Token, "AccountID" => $ID]);
        }
    }
?>