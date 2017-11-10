<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 15:07
 */

namespace Zsxsoft\AppValidator\Tasks;

use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\PathHelper;
use Zsxsoft\AppValidator\Wrappers\Config;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;
use Zsxsoft\W3CValidator\W3CValidator;

class RunBrowser
{
    public function run()
    {
        $electronPath = Config::get('electronPath');
        if (!$electronPath) {
          $electronPath = PathHelper::getAbsoluteFilename(ROOT_PATH . '/node_modules/electron/dist/electron');
        }
        $javascriptPath = PathHelper::getAbsoluteFilename(ROOT_PATH . '/javascript/browser/browser.js');
        
        $output = trim(shell_exec($electronPath . ' ' . escapeshellarg($javascriptPath)));
        try {
            $data = json_decode($output);
            var_dump($data);
        } catch (\Exception $e) {
            throw new \Exception('Parse output failed');
        }

    }

}