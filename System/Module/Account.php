<?php
    if (!defined("ROOT")) { exit(); }

    function UsernameIsAvailable($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : "";

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (strlen($Username) < 3)
            JSON(["Message" => 2]);

        if (strlen($Username) > 32)
            JSON(["Message" => 3]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 4]);

        $App->RateLimit->Call('UsernameIsAvailableQuery.1.2000');

        if (empty($App->DB->Find('account', ['Username' => $Username])->toArray()))
            JSON(["Message" => 1000]);

        JSON(["Message" => 5]);
    }

    function SignUp($App)
    {
        $Username = isset($_POST["Username"]) ? strtolower($_POST["Username"]) : "";
        $Password = isset($_POST["Password"]) ? $_POST["Password"] : "";
        $Email    = isset($_POST["Email"])    ? strtolower($_POST["Email"]) : "";
        $Session  = isset($_POST["Session"])  ? $_POST["Session"] : "";

        if (!isset($Username) || empty($Username))
            JSON(["Message" => 1]);

        if (!isset($Password) || empty($Password))
            JSON(["Message" => 2]);

        if (!isset($Email) || empty($Email))
            JSON(["Message" => 3]);

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            JSON(["Message" => 4]);

        if (strlen($Username) < 3)
            JSON(["Message" => 5]);

        if (strlen($Username) > 32)
            JSON(["Message" => 6]);

        if (strlen($Password) < 6)
            JSON(["Message" => 7]);

        if (strlen($Password) > 32)
            JSON(["Message" => 8]);

        if (strlen($Email) > 64)
            JSON(["Message" => 9]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Message" => 10]);

        $App->RateLimit->Call('SignUpQuery.1.2000');

        if (!empty($App->DB->Find('account', ['Username' => $Username])->toArray()))
            JSON(["Message" => 11]);

        if (!empty($App->DB->Find('account', ['Email' => $Email])->toArray()))
            JSON(["Message" => 12]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $App->RateLimit->Call('SignUpCreated.1.60000');

        $Time = time();

        $ID = $App->DB->Insert('account', ['Username' => $Username, 'Password' => password_hash($Password, PASSWORD_BCRYPT), 'Email' => $Email, 'CreatedTime' => $Time, 'LastOnline' => $Time]);

        $Token = $App->Auth->CreateToken(["ID" => $ID->__toString]);

        $App->DB->Update('account', ['_id' => $ID], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => $Time]]]);

        JSON(["Message" => 1000, "TOKEN" => $Token, "ID" => $ID]);
    }

    function SignIn($App)
    {
        $EmailOrUsername = isset($_POST["EmailOrUsername"]) ? strtolower($_POST["EmailOrUsername"]) : "";
        $Password        = isset($_POST["Password"])        ? $_POST["Password"] : "";
        $Session         = isset($_POST["Session"])         ? $_POST["Session"] : "";

        if (!isset($EmailOrUsername) || empty($EmailOrUsername))
            JSON(["Message" => 1]);

        if (!isset($Password) || empty($Password))
            JSON(["Message" => 2]);

        if (strlen($EmailOrUsername) < 3)
            JSON(["Message" => 3]);

        if (strlen($EmailOrUsername) > 64)
            JSON(["Message" => 4]);

        if (strlen($Password) < 6)
            JSON(["Message" => 5]);

        if (strlen($Password) > 32)
            JSON(["Message" => 6]);

        if (!filter_var($EmailOrUsername, FILTER_VALIDATE_EMAIL))
            if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $EmailOrUsername))
                JSON(["Message" => 7]);

        $App->RateLimit->Call('SignInQuery.1.2000');

        if (!filter_var($EmailOrUsername, FILTER_VALIDATE_EMAIL))
            $Account = $App->DB->Find('account', ['Username' => $EmailOrUsername])->toArray();
        else
            $Account = $App->DB->Find('account', ['Email' => $EmailOrUsername])->toArray();

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

        JSON(["Message" => 1000, "TOKEN" => $Token, "ID" => $ID, "Avatar" => (isset($Account[0]->Avatar) ? $Account[0]->Avatar : "")]);
    }

    function ResetPassword($App)
    {
        $EmailOrUsername = isset($_POST["EmailOrUsername"]) ? strtolower($_POST["EmailOrUsername"]) : "";

        if (!isset($EmailOrUsername) || empty($EmailOrUsername))
            JSON(["Message" => 1]);

        if (strlen($EmailOrUsername) < 3)
            JSON(["Message" => 2]);

        if (strlen($EmailOrUsername) > 64)
            JSON(["Message" => 3]);

        if (!filter_var($EmailOrUsername, FILTER_VALIDATE_EMAIL))
            if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $EmailOrUsername))
                JSON(["Message" => 4]);

        $App->RateLimit->Call('ResetPasswordQuery.1.2000');

        if (filter_var($EmailOrUsername, FILTER_VALIDATE_EMAIL))
            $Account = $App->DB->Find('account', ['Email' => $EmailOrUsername])->toArray();
        else
            $Account = $App->DB->Find('account', ['Username' => $EmailOrUsername])->toArray();

        if (empty($Account))
            JSON(["Message" => 5]);

        $RandomString = '';
        $App->RateLimit->Call('ResetPasswordDone.1.300000');
        $Characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        for ($I = 0; $I < 15; $I++) { $RandomString .= $Characters[rand(0, 61)]; }

        $RandomString .= str_rot13(strrev($Account[0]->Username));

        $App->DB->Insert('recovery_password', ['ID' => $Account[0]->_id, 'Username' => $Account[0]->Username, 'Email' => $Account[0]->Email, "Key" => $RandomString, 'CreatedTime' => time()]);

        # SendMail

        JSON(["Message" => 1000]);
    }

    function SignInGoogle($App)
    {
        $Token   = isset($_POST["Token"])   ? $_POST["Token"] : "";
        $Session = isset($_POST["Session"]) ? $_POST["Session"] : "";

        if (!isset($Token) || empty($Token))
            JSON(["Message" => 1]);

        require_once(ROOT. 'System/Library/GoogleAPI/vendor/autoload.php');

        $Client = new Google_Client();
        $PayLoad = $Client->verifyIdToken($Token);

        if (isset($PayLoad)
            JSON(["Message" => 2]);

        if ($PayLoad['iss'] != "accounts.google.com" && $PayLoad['iss'] != "https://accounts.google.com");
            JSON(["Message" => 3]);

        if ($PayLoad['aud'] != '590625045379-9pgbc6r8v0794rij59jj50o1gp6ijnvl.apps.googleusercontent.com');
            JSON(["Message" => 4]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $App->RateLimit->Call('SignInGoogleQuery.1.2000');
        $Account = $App->DB->Find('account', ['GoogleID' => $PayLoad['sub']])->toArray();

        if (empty($Account))
        {
            $App->RateLimit->Call('SignInGoogleCreated.1.60000');

            $ID = $App->DB->Insert('account', ['GoogleID' => $PayLoad['sub'], 'Username' => ("unknown" . time()), 'Email' => $PayLoad['email'], 'CreatedTime' => time()])->__toString();

            $Token = $App->Auth->CreateToken(["ID" => $ID]);

            $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);
            $Account = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($ID)])->toArray();

            JSON(["Message" => 1000, "TOKEN" => $Token, "ID" => $ID, "Username" => $Account[0]->Username]);
        }
        else
        {
            $ID = $Account[0]->_id->__toString();

            $Token = $App->Auth->CreateToken(["ID" => $ID]);

            $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

            JSON(["Message" => 1000, "TOKEN" => $Token, "ID" => $ID, "Avatar" => (isset($Account[0]->Avatar) ? $Account[0]->Avatar : "")]);
        }
    }
?>