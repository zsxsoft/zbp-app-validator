<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/22
 * Time: 16:46
 */

namespace Zsxsoft\AppValidator\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\ServerManager;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Helpers\ZBPInstaller;

class StartProject extends Command
{

    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start a new check project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Logger::info('Starting a new check project...');
        TempHelper::createTemp();
        ZBPInstaller::createEmptyEnvironment();
        ServerManager::start();
    }
}