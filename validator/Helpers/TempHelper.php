<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/22
 * Time: 11:13
 */

namespace Zsxsoft\AppValidator\Helpers;

class TempHelper
{
    use StaticInstance;

    protected $path = '';

    public function __construct()
    {
        $this->path = PathHelper::getAbsoluteFilename(dirname(__FILE__) . '/../../tmp');
    }

    protected function createTemp()
    {
        @mkdir($this->path);
    }

    protected function getPath($path)
    {
        return PathHelper::getAbsoluteFilename($this->path . $path);
    }
}