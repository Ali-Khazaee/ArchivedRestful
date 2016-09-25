<?php
    class Account
    {
        public static function Register()
        {
            $db = new Database();
            $db->insert('users',
                [
                   'name' => 'test'
                ]);

        }
    }
?>