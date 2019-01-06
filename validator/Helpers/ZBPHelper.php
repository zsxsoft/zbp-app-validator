<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 11:32
 */

namespace Zsxsoft\AppValidator\Helpers;

use Zsxsoft\AppValidator\Wrappers\Config;

class ZBPHelper
{
    use StaticInstance;
    protected $path = '';
    protected $git = false;

    public function __construct()
    {
        $zbpPath = Config::get('zbpPath');
        $this->path = $zbpPath == false ? TempHelper::getPath('/web') : $zbpPath;
    }

    protected function getPath($path = null)
    {
        return PathHelper::getAbsoluteFilename($this->path . (is_null($path) ? '' : $path));
    }

}