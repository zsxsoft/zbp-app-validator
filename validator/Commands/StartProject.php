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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Wrappers\ServerManager;
use Zsxsoft\AppValidator\Wrappers\ZBPInstaller;

class StartProject extends Command
{

    protected function configure()
    {
        $this
            ->setName('project:start')
            ->setDescription('Clean the temporary and start a new check project')
            ->addOption(
                'start-server',
                null,
                InputOption::VALUE_OPTIONAL,
                'Start PHP test server',
                true
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Logger::info('Starting a new check project...');
        TempHelper::createTemp();
        ZBPInstaller::createEmptyEnvironment();
        if ($input->getOption('start-server')) {
            ServerManager::start();
        }
    }
}