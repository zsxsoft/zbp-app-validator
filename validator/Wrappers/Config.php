<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 10:47
 */

namespace Zsxsoft\AppValidator\Wrappers;

use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\StaticInstance;

class Config
{
    use StaticInstance;
    private $_items = [];

    public function __construct()
    {
        try {
            $default = json_decode(file_get_contents(ROOT_PATH . '/config.default.json'), true);
            $custom = json_decode(file_get_contents(ROOT_PATH . '/config.json'), true);
            $this->_items = array_merge($default, $custom);
        } catch (\Exception $e) {
            Logger::error('Parse Config Error!');
            exit;
        }
    }

    protected function get($key)
    {
        if (isset($this->_items[$key])) {
            return $this->_items[$key];
        }
        return null;
    }
}
