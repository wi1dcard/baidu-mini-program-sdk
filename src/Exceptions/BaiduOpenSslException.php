<?php

namespace BaiduMiniProgram\Exceptions;

/**
 * 当 OpenSSL 函数返回失败时抛出。
 */
class BaiduOpenSslException extends BaiduException
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if ($message == '') {
            $message = openssl_error_string();
        }
        parent::__construct($message, $code, $previous);
    }
}
