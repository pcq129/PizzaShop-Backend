<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

class Helper
{
   

    public static function sendResponse($code = '', $status = true, $data = null, $message = '')
    {

        $status_codes = [
            'ok' => 200,
            'created' => 201,
            'no_content' => 204,
            'not_found' => 404,
            'found' => 302,
            'unauthorized' => 401,
            'forbidden' => 403,
            'method_not_allowed' => 405,
            'error' => 500,
            'bad_request' => 400
        ];
    
        $default_messages = [
            'ok' => 'Request successful.',
            'created' => 'Resource created successfully.',
            'no_content' => 'No content available.',
            'not_found' => 'Resource not found.',
            'found' => 'Resource found.',
            'unauthorized' => 'Unauthorized access.',
            'forbidden' => 'Access forbidden.',
            'method_not_allowed' => 'Method not allowed.',
            'error' => 'Internal server error'
        ];

        $code = isset($status_codes[$code]) ? $status_codes[$code] : 500;
        $string_message = isset($default_messages[$message]) ? $default_messages[$message] : $message;
        // if ($data && $code != 500) {
        //     $code = 204;
        // }

        return response()->json([
            'code' => $code,
            'status' => $status,
            'data' => $data,
            'message' => $string_message,
        ], $code);
    }
}