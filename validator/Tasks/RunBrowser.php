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
        $nodePath = Config::get('nodePath');
        if (!$nodePath) {
            $currentPath = PathHelper::getAbsoluteFilename(ROOT_PATH . '/bin/node');
            if (file_exists($currentPath)) {
                $nodePath = $currentPath;
            } else {
                $nodePath = 'node';
            }
        }
        $javascriptPath = PathHelper::getAbsoluteFilename(ROOT_PATH . '/javascript/browser/browser.js');

        $output = trim(shell_exec($nodePath . ' ' . escapeshellarg($javascriptPath)));
        try {
            $data = json_decode($output);
        } catch (\Exception $e) {
            throw new \Exception('Parse output failed');
        }

        if (count($data->console) > 0) {
            foreach ($data->console as $console) {
                $type = 'info';
                if ($console->type === 'error') {
                    $type = 'error';
                } else if ($console->type === 'warning') {
                    $type = 'warn';
                }
                Logger::$type("{$console->count} * Console: [{$console->text}] first occurred on {$console->device} at {$console->url}");
            }
        }

        if (count($data->network) > 0) {
            foreach ($data->network as $request) {
                $method = 'info';
                if ($request->status != 200) {
                    $method = 'warn';
                }
                Logger::$method("{$request->count} * Network request: {$request->status}, {$request->url} (Size: {$request->length})");
            }
        }

        Logger::info('Screenshot taken');

    }

}
