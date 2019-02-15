<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 14:49
 */

namespace Zsxsoft\AppValidator\Tasks\StaticCodeScanner;


use Zsxsoft\AppValidator\Helpers\Logger;

class ErrorOutputter
{
    public $path = '';

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function curl_($item)
    {
        Logger::warn("Using curl in {$this->path}, Line {$item['line']}");
        Logger::warn("You'd better use the Class Network to replace it.");
    }

    public function eval_($item)
    {
        Logger::error("Using eval in {$this->path}, Line {$item['line']}");
    }

    public function system_($item)
    {
        Logger::error("Calling {$item['data']} in {$this->path}, Line {$item['line']}");
    }

    public function session_($item)
    {
        Logger::warn("Calling {$item['data']} in {$this->path}, Line {$item['line']}");

        if ($item['data'] == 'session_write_close') {
            Logger::warn('You\'d better use $zbp->EndSession() to replace it.');
        } else {
            Logger::warn('You\'d better use $zbp->StartSession() to replace it.');
        }
    }

}