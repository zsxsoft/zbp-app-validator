<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 15:07
 */

namespace Zsxsoft\AppValidator\Tasks;

use function Sodium\version_string;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\PathHelper;
use Zsxsoft\AppValidator\Helpers\PHPHelper;
use Zsxsoft\AppValidator\Wrappers\Config;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;
use Zsxsoft\W3CValidator\W3CValidator;

class PHPCompatibility
{
    public function run()
    {
        $data = null;

        $minimumPHPVersion = ZBPWrapper::getApp()->phpver;
        if (version_compare($minimumPHPVersion, "5.2", '<')) {
            $minimumPHPVersion = "5.2";
        }

        Logger::info('Running PHPCompatibility..');
        Logger::info("Version: $minimumPHPVersion to latest");
        $output = trim(
            shell_exec(
                '"' . PHPHelper::getBinary() . '"' . ' ' .
                escapeshellarg(PathHelper::getAbsoluteFilename(ROOT_PATH . '/vendor/squizlabs/php_codesniffer/bin/phpcs')) . ' ' .
                " --standard=PHPCompatibility --runtime-set testVersion $minimumPHPVersion- --report=json -p " .
                escapeshellarg(ZBPWrapper::getAppPath())
            )
        );

        $output = substr($output, strpos($output, '(100%)') + 6);

        try {
            $output = preg_replace('/Time: .+; Memory: .+$/i', '', $output);
            $data = json_decode($output);
        } catch (\Exception $e) {
            throw new \Exception('Parse output failed');
        }

        $sum = 0;
        /*
        if ($data->totals->errors === 0 && $data->totals->warnings === 0) {
            return;
        }
        */

        foreach ($data->files as $fileName => $file) {
            foreach ($file->messages as $error) {
                if (in_array($error->source, ["Internal.Tokenizer.Exception", "Internal.NoCodeFound"])) {
                    continue;
                }
                Logger::error($fileName . ': ');
                Logger::error("Line {$error->line}: {$error->message}");
                $sum++;
            }
        }
        if ($sum == 0) {
            Logger::info("Check PHP Compatibility finished.");
        } else {
            Logger::warning('You\'d better check these problems.');
        }

    }

}
