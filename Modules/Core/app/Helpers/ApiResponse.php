<?php

namespace Modules\Core\Helpers;

class ApiResponse
{
    public static function success($data, $status = 200, $message = 'Success')
    {
        return response()->json([
            'status' => true,
            'message' => $message, 'data' => $data,
        ], $status);
    }

    public static function error($status, $message = 'Error', $data = null)
    {

        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
