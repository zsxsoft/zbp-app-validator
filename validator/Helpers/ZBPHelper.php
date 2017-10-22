<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/22
 * Time: 16:59
 */

namespace Zsxsoft\AppValidator\Helpers;


class ZBPHelper
{
    use StaticInstance;
    protected $zbp = null;

    public function __construct()
    {
        global $zbp;
        require '../../tmp/web/zb_system/function/c_system_base.php';
        $this->zbp = $zbp;
        $zbp->Load();
    }

    public static function installApp($filePath)
    {
        $xmlData = file_get_contents($filePath);
        $charset = array();
        $charset[1] = substr($xmlData, 0, 1);
        $charset[2] = substr($xmlData, 1, 1);
        if (ord($charset[1]) == 31 && ord($charset[2]) == 139) {
            if (function_exists('gzdecode')) {
                $xmlData = gzdecode($xmlData);
            }
        }

        $appObject = simplexml_load_string($xmlData);
        $appId = $appObject->id;

        return (\App::UnPack($xmlData)) ? $appId : false;
    }

}