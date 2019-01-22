<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 10:36
 */

namespace Zsxsoft\AppValidator\Tasks;

use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\PHPHelper;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;

class ScanGlobalVariables
{

    private $_store = [];

    /**
     * Load globals and save them to the store
     *
     * @param string   $class
     * @param callable $callback
     */
    public function loadGlobals($class, callable $callback)
    {
        $this->_store[$class] = [
            "callback" => $callback,
            "data" => $callback(),
        ];
    }

    /**
     * Compare new data and original data
     *
     * @param string className $class
     *
     * @return array
     */
    public function diffGlobals($class)
    {
        return array_diff($this->_store[$class]['callback'](), $this->_store[$class]['data']);
    }

    /**
     * Check the name of functions
     *
     * @param array $diff
     */
    public function checkFunctions($diff)
    {

        $app = ZBPWrapper::getApp();
        Logger::info('Testing functions');
        $regex = str_replace("!!", $app->id, "/^(activeplugin_|installplugin_|uninstallplugin_)!!$|^!!_|^!!$|_!!$/si");
        //var_dump($diff);exit;
        foreach ($diff as $index => $name) {
            if (preg_match($regex, $name)) {
                Logger::info("Tested function: $name");
            } else {
                Logger::error("Illegal function name: $name");
                if ($ret = PHPHelper::getFunctionDescription($name)) {
                    Logger::error("In " . $ret->getFileName());
                    Logger::error("Line " . ($ret->getStartLine() - 1) . " To " . ($ret->getEndLine() - 1));
                }
            }
        }
    }

    /**
     * Check global variables / constants / class
     *
     * @param string $class
     * @param array  $diff
     *
     * @return bool
     **/
    public function checkOthers($class, $diff)
    {

        $app = ZBPWrapper::getApp();
        Logger::info('Testing ' . $class);
        $regex = str_replace("!!", $app->id, "/^!!_?/si");
        foreach ($diff as $index => $name) {
            if (preg_match($regex, $name)) {
                Logger::info('Tested ' . $class . ': ' . $name);
            } else {
                Logger::error('Illegal ' . $class . ': ' . $name);
            }
        }

        return true;
    }

    /**
     * Call check functions
     *
     * @param string $class
     *
     * @return bool
     */
    public function checkDiff($class)
    {
        $diff = $this->diffGlobals($class);
        $function = 'Check' . ucfirst($class);
        if (method_exists(__CLASS__, $function)) {
            return call_user_func(array(__CLASS__, $function), $diff);
        }

        return $this->checkOthers($class, $diff);
    }

    /**
     * Runner
     */
    public function run()
    {
        $zbp = ZBPWrapper::getZbp();
        $app = ZBPWrapper::getApp();
        new PHPHelper(); // Include the helper to prevent sub-standard warning

        Logger::info('Scanning functions and global variables');
        $filename = $zbp->path . '/zb_users/' . $app->type . '/' . $app->id . '/include.php';
        $this->loadGlobals(
            'variables', function () {
                return array_keys($GLOBALS);
            }
        );
        $this->loadGlobals(
            'functions', function () {
                return get_defined_functions()['user'];
            }
        );
        $this->loadGlobals(
            'constants', function () {
                return array_keys(get_defined_constants());
            }
        );
        $this->loadGlobals(
            'classes', function () {
                return get_declared_classes();
            }
        );

        $includeFlag = PHPHelper::includeFile($filename);
        if ($includeFlag === true) {
            if (!is_readable($filename)) {
                Logger::info('No include file.');
            } else {
                Logger::warning('You\'d better disable this app before check.');
            }
            return;
        }

        $this->checkDiff('variables');
        $this->checkDiff('functions');
        $this->checkDiff('constants');
        $this->checkDiff('classes');
    }
}
