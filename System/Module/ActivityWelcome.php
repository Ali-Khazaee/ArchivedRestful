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

        $App->RateLimit->Call('ActivityWelcomeEmailSignQuery.1.5000');

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

        $App->RateLimit->Call('ActivityWelcomeSignInQuery.1.3000');

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

    function ActivityWelcomeSignInGoogle($App)
    {
        require_once ROOT. 'aaa/vendor/autoload.php';
    
    $TOKEN = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjcxMDY4YTFjYzM0OTQ3MmE5ZTdiZmRkNTQ0MDM5NDlmM2VkMjgzY2UifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJpYXQiOjE0ODg5MDAyMjUsImV4cCI6MTQ4ODkwMzgyNSwiYXVkIjoiNTkwNjI1MDQ1Mzc5LTlwZ2JjNnI4djA3OTRyaWo1OWpqNTBvMWdwNmlqbnZsLmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwic3ViIjoiMTA4NjMzMTkzMzY4MzkwODcwMDU1IiwiZW1haWxfdmVyaWZpZWQiOnRydWUsImF6cCI6IjU5MDYyNTA0NTM3OS1rNzBwdG5raGo0OWJhZW81bWtwcnBvZDlhczU4c3JtZS5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsImVtYWlsIjoiZGV2LmtoYXphZWVAZ21haWwuY29tIiwibmFtZSI6IkFsaSBLaGF6YWVlIiwicGljdHVyZSI6Imh0dHBzOi8vbGg0Lmdvb2dsZXVzZXJjb250ZW50LmNvbS8tUHB1U09hSFBOalkvQUFBQUFBQUFBQUkvQUFBQUFBQUFBQUEvQUFvbXZWMWt6T0I2RG92aEpXYnVxMDdYVmllV1FZRzJQZy9zOTYtYy9waG90by5qcGciLCJnaXZlbl9uYW1lIjoiQWxpIiwiZmFtaWx5X25hbWUiOiJLaGF6YWVlIiwibG9jYWxlIjoiZW4ifQ.gPuPph7GFZG89jKTDQFdtF4y2XLFl6-XP5kFuc-kvz14k69J42RCZRCCOBo4_-wEBTizZno57nf8pTbAN3tlbg30HfReQpdlJ5DigbDv1Jy8UvlovMFkqMBjnAIU9eCeCMjQa3Du82wGZxDKMqzOLU4kJXljqwgUHWJ0gv3LHubFlMwtmXmrCMB4UWE2_PVhdIqMeQYK0vDVrwcQydc--i9XVO9aoh_KdYZ0P4vUWhXE0JJD2C3blbtqSZDZut24kXXecdyJ07chgHW9n4LklHrd7rjv83QO27MicmFrNRJ0bpebb9EPcdUFpnOnN1xGP3IspUXC2y2YSB-0B8Qjxw";

    $Client = new Google_Client();
    $PayLoad = $Client->verifyIdToken($TOKEN);

    echo "<pre>";
    var_dump($PayLoad);
    echo "</pre>";

    if ($PayLoad)
    {
        $userid = $PayLoad['sub'];
        // If request specified a G Suite domain:
        //$domain = $payload['hd'];
    }
    else
    {
        // Invalid ID token
    }
    
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

        $App->RateLimit->Call('ActivityWelcomeSignInQuery.1.3000');

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

    /*function SignOut($App)
    {
        $Token = $_SERVER['HTTP_TOKEN'];

        if (!isset($Token) || empty($Token))
            JSON(["Message" => 1]);

        $Decode = $App->Auth->Decode($Token);
        $ID = $Decode->ID;

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$pull' => ['Session' => ["Token" => $Token]]]);

        Logger($App, 'SignOut',['UserID' => $ID]);

        JSON(["Message" => 1000)]);
    }*/
?>