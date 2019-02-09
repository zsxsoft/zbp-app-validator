<?php
/**
 * Bootstrap
 *
 * @package AppChecker
 * @author  zsx <zsx@zsxsoft.com>
 * @php     >= 5.3
 **/
namespace AppChecker;

use Symfony\Component\Console\Application;


$application = new Application();

$application->add(new \Zsxsoft\AppValidator\Commands\App\Login());
$application->add(new \Zsxsoft\AppValidator\Commands\App\Enable());
$application->add(new \Zsxsoft\AppValidator\Commands\App\ChangeTheme());
$application->add(new \Zsxsoft\AppValidator\Commands\App\Extract());

$application->add(new \Zsxsoft\AppValidator\Commands\Project\Start());
$application->add(new \Zsxsoft\AppValidator\Commands\Project\End());

$application->add(new \Zsxsoft\AppValidator\Commands\Scanner\ScanGlobalVariables());
$application->add(new \Zsxsoft\AppValidator\Commands\Scanner\ScanStaticCode());
$application->add(new \Zsxsoft\AppValidator\Commands\Scanner\ValidateW3C());
$application->add(new \Zsxsoft\AppValidator\Commands\Scanner\PHPCompatibility());

$application->add(new \Zsxsoft\AppValidator\Commands\StartPipe());
$application->add(new \Zsxsoft\AppValidator\Commands\RunBrowser());
$application->add(new \Zsxsoft\AppValidator\Commands\StartServer());
$application->run();
