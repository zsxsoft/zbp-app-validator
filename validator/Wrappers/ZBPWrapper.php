<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/22
 * Time: 16:59
 */

namespace Zsxsoft\AppValidator\Wrappers;

use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\StaticInstance;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Helpers\ZBPHelper;

/**
 * Class ZBPWrapper
 * @package Zsxsoft\AppValidator\Wrappers
 * @method static \App getApp()
 * @method static \ZBlogPHP getZbp()
 */
class ZBPWrapper
{
    use StaticInstance;
    /**
     * @var \ZBlogPHP
     */
    protected $zbp = null;
    /**
     * @var \App
     */
    protected $app = null;

    public function __construct()
    {
        global $zbp;
        require ZBPHelper::getPath('/zb_system/function/c_system_base.php');
        $this->zbp = $zbp;
        $zbp->Load();
    }

    protected function loadApp($appId)
    {
        $zbp = $this->zbp;
        $app = $zbp->LoadApp('plugin', $appId);
        if (is_null($app->id)) {
            $app = $zbp->LoadApp('theme', $appId);
        }
        if (is_null($app->id)) {
            Logger::error("Load $appId failed!");
            exit;
        }
        $this->app = $app;
        return $app;
    }

    protected function installApp($filePath)
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


    protected function changeTheme()
    {
        \SetTheme($this->app->id, array_keys($this->app->GetCssFiles())[0]);
        $this->zbp->template->SetPath($this->zbp->usersdir . 'cache/compiled/' . $this->app->id . '/');
        $this->zbp->BuildModule();
        $this->zbp->SaveCache();
        Logger::info('Compiling Theme..');
        // @TODO Maybe a ZBP's bug
        $this->zbp->template->theme = $this->app->id;
        $this->zbp->CheckTemplate(false, true);
        Logger::info("Theme changed to {$this->app->id}");
    }
}