<?php
    class Account
    {
        public static function Register($App)
        {
            $App->DB->insert('users',
            [
                'name' => 'test'
            ]);
        }
    }
?>