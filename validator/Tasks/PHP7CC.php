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

class PHP7CC
{
    public function run()
    {
        $data = null;

        Logger::info('Running PHP7CC..');
        $output = trim(shell_exec(
          '"' . PHP_BINARY . '"' . ' ' .
          escapeshellarg(PathHelper::getAbsoluteFilename(ROOT_PATH . '/vendor/php7cc.phar')) . ' ' . 
          ' -o json ' . 
          escapeshellarg(ZBPWrapper::getAppPath())
          )
        );

        try {
            $data = json_decode($output);
        } catch (\Exception $e) {
            throw new \Exception('Parse output failed');
        }


        if (count($data->files) == 0) {
            Logger::info("Check PHP 7 Compatibility success, scanned {$data->summary->checkedFiles} files");
            return;
        }

        foreach ($data->files as $file) {
            foreach ($file->errors as $error) {
                Logger::error($file->name . ': ');
                Logger::error("Line {$error->line}: {$error->text}");
            }
            foreach ($file->warnings as $warning) {
                Logger::warning($file->name . ': ');
                Logger::warning("Line {$warning->line}: {$warning->text}");
            }
        }
        Logger::error('Please fix the errors that will not compatible with PHP 7');

    }

}