<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 15:07
 */

namespace Zsxsoft\AppValidator\Tasks;

use Cocur\BackgroundProcess\BackgroundProcess;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\MeaningfulBackgroundProcess;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Wrappers\Config;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;
use Zsxsoft\W3CValidator\W3CValidator;

class StartPipe
{
    public function run($appId)
    {
        $type = 'plugin';

        $this->startProcess('project:start --start-server=0');
        // Start server in background
        $process = new MeaningfulBackgroundProcess($this->getCommandLine('server:start'));
        $process->run();
        if (preg_match('/\.g?zba$/', $appId)) {
            $this->startProcess('extract', $appId);
            $appId = file_get_contents(TempHelper::getPath('/extracted'));
        }

        $zbp = ZBPWrapper::getZbp();
        $app = ZBPWrapper::loadApp($appId);
        $type = $app->type;

        $this->startProcess('scan:variable', $appId);

        if ($type == 'theme') {
            $this->startProcess('theme:change', $appId);
            $this->startProcess('scan:code', $appId);
            $this->startProcess('scan:w3c');
        } else {
            $this->startProcess('scan:code', $appId);
        }

        $this->startProcess('project:end');

    }

    private function startProcess($command, $argument = '') {
        passthru($this->getCommandLine($command, $argument));
    }

    private function getCommandLine($command, $argument = '') {
        $path = escapeshellarg(ROOT_PATH . DIRECTORY_SEPARATOR . 'checker');
        $arg = $argument === '' ? '' : escapeshellarg($argument);
        return "php $path $command $arg";
    }



}