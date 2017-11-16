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
        $data = null;
        Logger::info('Running browser..');
        $electronPath = Config::get('electronPath');
        if (!$electronPath) {
          $electronPath = PathHelper::getAbsoluteFilename(ROOT_PATH . '/node_modules/electron/dist/electron');
        }
        $javascriptPath = PathHelper::getAbsoluteFilename(ROOT_PATH . '/javascript/browser/browser.js');
        
        $output = trim(shell_exec($electronPath . ' ' . escapeshellarg($javascriptPath)));
        try {
            $data = json_decode($output);
        } catch (\Exception $e) {
            throw new \Exception('Parse output failed');
        }

        if (count($data->console) > 0) {
            foreach ($data->console as $message) {
                Logger::info("Console message: " . $message);
            }
        }

        if (count($data->network) > 0) {
            foreach ($data->network as $request) {
                $method = 'info';
                if ($request->statusCode != 200) {
                    $method = 'warn';
                }
                Logger::$method("Network request: {$request->statusCode}, {$request->url}");
            }
        }

        Logger::info('Screenshot taken');

    }

}