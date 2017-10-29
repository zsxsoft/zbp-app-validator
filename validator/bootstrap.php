<?php
/**
 * api
 * @author zsx<zsx@zsxsoft.com>
 * @package api/route/error
 * @php >= 5.3
 */
namespace AppChecker;

use Symfony\Component\Console\Application;
use Zsxsoft\AppValidator\Commands\EndProject;
use Zsxsoft\AppValidator\Commands\ExtractApp;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Helpers\ZBPInstaller;
use Zsxsoft\AppValidator\Commands\StartProject;


$application = new Application();
$application->add(new StartProject());
$application->add(new EndProject());
$application->add(new ExtractApp());
$application->run();

/*
if (ZBPInstaller::isUsingGit()) {

}
*/

//ZBPInstaller::extract();
//Logger::warning('test');

/*
$path = $config->ZBPPath;
if (!is_dir($path) || !chdir($path)) {
    echo 'Cannot open your Z-BlogPHP index.php: ' . $path;
    exit;
}


$zbp->Load();
ErrorHandler::Hook();
ob_flush();

$application->add(new Run());
$application->add(new Install());
$application->run();
*/