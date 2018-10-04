<?php

namespace BaiduMiniProgram\Exceptions;

/**
 * 当 Base64 编解码失败时抛出。
 */
class BaiduBase64Exception extends BaiduException
{
    public function __construct($value, $isEncoding = false)
    {
        $verb = $isEncoding ? 'encoded' : 'decoded';
        parent::__construct("Value `{$value}` cound not be {$verb}");
    }
}
