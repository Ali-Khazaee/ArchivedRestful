<?php

    // Api json response
    function json_response($status = 'fail', $message = '', array $data = null , $code = 200)
    {
        // clear old headers
        header_remove();

        // set http code
        http_response_code($code);

        // set json type to header
        header('Content-Type: application/json');

        // return the encoded json
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);

        exit;
    }