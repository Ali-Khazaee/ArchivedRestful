<?php

    class Account
    {

        public static function Register($App)
        {

            // grab raw data from request and decode
            $data = json_decode(file_get_contents("php://input"));

            if( isset($data->name) || !empty($data->name) ){
                $name = $data->name;
            } else {
                json_response('fail', 'name and password are required!');
            }

            if( isset($data->password) || !empty($data['password']) ){
                $password = password_hash($data->password, PASSWORD_BCRYPT);
            } else {
                json_response('fail', 'name and password are required!');
            }


            $user = $App->DB->find('users', [
                'name' => $name
            ])->toArray();

            if(empty($user)){

                $App->DB->Insert('users', [
                    'name' => $name,
                    'password' => $password
                ]);

                json_response('success', 'Registration was successfull!');

            } else {
                json_response('fail', 'This name already has taken. Choose another name.');
            }
        }


    }
