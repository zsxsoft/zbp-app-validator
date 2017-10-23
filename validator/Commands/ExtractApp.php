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
use Zsxsoft\AppValidator\Helpers\ZBPHelper;
use Zsxsoft\AppValidator\Helpers\ZBPInstaller;

class ExtractApp extends Command
{

    protected function configure()
    {
        $this
            ->setName('extract')
            ->setDescription('Extract a app from .zba file')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The Path of ZBA File'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appPath = $input->getArgument("path");
        if (!file_exists($appPath)) {
            Logger::error("$appPath not found or unreadable");
            return;
        }
        $appId = ZBPHelper::installApp($appPath);
        if ($appId == false) {
            Logger::error("Extract $appPath failed");
            return;
        }
        Logger::info("Extracted $appId");
    }
}