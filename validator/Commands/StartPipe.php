<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/23
 * Time: 19:39
 */

namespace Zsxsoft\AppValidator\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Tasks\StartPipe as Task;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;

class StartPipe extends Command
{

    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start validator')
            ->addArgument(
                'appId',
                InputArgument::REQUIRED,
                'App ID or ZBA Path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appId = $input->getArgument("appId");
        (new Task())->run($appId);
    }
}