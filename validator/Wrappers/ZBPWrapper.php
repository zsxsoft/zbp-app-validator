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
 *
 * @package Zsxsoft\AppValidator\Wrappers
 * @method  static \App getApp()
 * @method  static \ZBlogPHP getZbp()
 */
class ZBPWrapper
{
    use StaticInstance;

    /**
     * Z-BlogPHP Global Object
     *
     * @var \ZBlogPHP
     */
    protected $zbp = null;

    /**
     * Z-BlogPHP App Object
     *
     * @var \App
     */
    protected $app = null;

    public function __construct()
    {
        global $zbp;
        include ZBPHelper::getPath('/zb_system/function/c_system_base.php');
        $this->zbp = $zbp;
        $zbp->Load();
    }


    protected function getAppPath()
    {
        $app = self::getApp();
        return ZBPHelper::getPath() . '/zb_users/' . $app->type . '/' . $app->id . '/';
    }

    protected function loadApp($appId, $temp = false)
    {
        $zbp = $this->zbp;
        $app = $zbp->LoadApp('plugin', $appId);
        if (is_null($app->name)) {
            $app = $zbp->LoadApp('theme', $appId);
        }
        if (is_null($app->name)) {
            Logger::error("Load $appId failed!");
            exit;
        }
        if (!$temp) {
            $this->app = $app;
        }
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

        if (\App::UnPack($xmlData)) {
            file_put_contents(TempHelper::getPath('/extracted'), $appId);
            return $appId;
        }
        return false;
    }


    protected function changeTheme()
    {
        $this->installDependencies($this->app);
        $cssFiles = $this->app->GetCssFiles();
        if (count($cssFiles) === 0) {
            Logger::error('No CSS file, theme invalid');
            exit(1);
        }
        \SetTheme($this->app->id, array_keys($cssFiles)[0]);
        $this->zbp->template->SetPath($this->zbp->usersdir . 'cache/compiled/' . $this->app->id . '/');
        $this->zbp->BuildModule();
        $this->zbp->SaveCache();
        $this->zbp->template->theme = $this->app->id;
        $this->enableApp($this->app->id, 'theme');
        Logger::info('Compiling Theme..');
        // @TODO Maybe a ZBP's bug
        $this->zbp->CheckTemplate(false, true);
        Logger::info("Theme changed to {$this->app->id}");
    }

    protected function enablePlugin()
    {
        $this->installDependencies($this->app);
        $this->enableApp($this->app->id, 'plugin');
        Logger::info("Enabled {$this->app->id}");
    }

    protected function installDependencies($app)
    {
        $dependencies = explode('|', $app->advanced_dependency);
        foreach ($dependencies as $dependency) {
            if (!$dependency) {
                continue;
            }
            if (!in_array($dependency, $this->zbp->activedapps)) {
                $this->installDependency($dependency);
            }
        }
    }

    protected function installDependency($appId)
    {
        Logger::info("Installing Dependency: $appId");
        $app = AppCenterWrapper::installAppFromRemote($appId);
        if (!$app) {
            return false;
        }
        $this->enableApp($app->id, 'plugin');
        $this->installDependencies($app);
        // @TODO Maybe another ZBP Bug
        $this->zbp->activeapps[] = $app->id;
        Logger::info("Enabled {$app->id}");
    }

    protected function enableApp($appId, $type)
    {
        if ($type === 'plugin') {
            \EnablePlugin($appId);
        }
        Logger::info("Calling InstallPlugin_" . $appId);
        require_once $this->zbp->usersdir . $type . '/' . $appId . '/include.php';
        \InstallPlugin($appId);
    }

}
