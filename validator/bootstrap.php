<?php
/**
 * api
 * @author zsx<zsx@zsxsoft.com>
 * @package api/route/error
 * @php >= 5.3
 */
namespace AppChecker;

use Symfony\Component\Console\Application;
use Zsxsoft\AppValidator\Commands\ChangeTheme;
use Zsxsoft\AppValidator\Commands\RunBrowser;
use Zsxsoft\AppValidator\Commands\ScanGlobalVariables;
use Zsxsoft\AppValidator\Commands\EndProject;
use Zsxsoft\AppValidator\Commands\ExtractApp;
use Zsxsoft\AppValidator\Commands\ScanStaticCode;
use Zsxsoft\AppValidator\Commands\StartPipe;
use Zsxsoft\AppValidator\Commands\StartServer;
use Zsxsoft\AppValidator\Commands\ValidateW3C;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Commands\StartProject;


$application = new Application();
$application->add(new StartProject());
$application->add(new EndProject());
$application->add(new ExtractApp());
$application->add(new ScanGlobalVariables());
$application->add(new ScanStaticCode());
$application->add(new ChangeTheme());
$application->add(new ValidateW3C());
$application->add(new RunBrowser());
$application->add(new StartServer());
$application->add(new StartPipe());
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