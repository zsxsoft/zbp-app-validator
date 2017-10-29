<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/22
 * Time: 11:11
 */

namespace Zsxsoft\AppValidator\Wrappers;

use GuzzleHttp\Client;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\PathHelper;
use Zsxsoft\AppValidator\Helpers\StaticInstance;
use Zsxsoft\AppValidator\Helpers\TempHelper;
use Zsxsoft\AppValidator\Helpers\ZBPHelper;


class ZBPInstaller
{
    use StaticInstance;
    protected $downloadUrl = 'https://update.zblogcn.com/zblogphp/?install';
    protected $cloneUrl = 'https://github.com/zblogcn/zblogphp';
    protected $xmlPath = '';
    protected $gitPath = '';
    protected $webPath = '';
    protected $git = false;

    public function __construct()
    {
        $this->xmlPath = TempHelper::getPath('/zblogphp.xml');
        $this->gitPath = TempHelper::getPath('/git');
        $this->webPath = ZBPHelper::getPath();
        if (is_dir(TempHelper::getPath('/git/.git'))) {
            $this->git = true;
        }
    }


    protected function isUsingGit()
    {
        return $this->git;
    }

    protected function download()
    {
        $client = new Client();
        $resource = fopen($this->xmlPath, 'w');
        $client->request('GET', $this->downloadUrl, ['sink' => $resource]);
    }

    protected function gitClone()
    {
        `git clone {$this->cloneUrl} {$this->gitPath}`;
        $this->git = true;
    }

    protected function gitPull()
    {
        `git pull ${$this->gitPath}`;
    }

    protected function gitVersion()
    {
        chdir($this->gitPath);
        $ret = `git log --pretty="%h" -n1 HEAD`;
        chdir('../../');
        return $ret;
    }

    protected function extract()
    {
        $webPath = $this->webPath;
        PathHelper::rrmdir($webPath);
        TempHelper::createTemp();
        @mkdir($webPath);

        if ($this->isUsingGit()) {
            PathHelper::rcopy($this->gitPath, $webPath);
        } else {
            $xml = simplexml_load_file($this->xmlPath, 'SimpleXMLElement');
            $old = umask(0);
            foreach ($xml->file as $f) {
                $filename = $webPath . DIRECTORY_SEPARATOR . str_replace('\\', '/', $f->attributes());
                $dirname = dirname($filename);
                mkdir($dirname, 0755, true);
                file_put_contents($filename, base64_decode($f));
            }
        }
    }

    protected function createEmptyEnvironment()
    {
        if (!$this->isUsingGit()) {
            if (!is_file($this->xmlPath)) {
                Logger::info('Downloading latest Z-BlogPHP...');
                $this->download();
            }
        }
        Logger::info('Cleaning Environment');
        if ($this->isUsingGit()) {
            $version = ZBPInstaller::gitVersion();
            Logger::info("Using Git {$version}Z-BlogPHP");
        } else {
            Logger::info('Using latest stable Z-BlogPHP');
        }
        $this->extract();

        PathHelper::rcopy(PathHelper::getAbsoluteFilename(ROOT_PATH . '/resources/zb_users'), $this->webPath . '/zb_users');

        Logger::info('Cleaned successfully');
    }

}