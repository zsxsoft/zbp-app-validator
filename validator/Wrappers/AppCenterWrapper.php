<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 11/16/2017
 * Time: 15:10
 */

namespace Zsxsoft\AppValidator\Wrappers;

use DOMDocument;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\StaticInstance;

class AppCenterWrapper
{
    use StaticInstance;
    protected $config = [];
    protected $cookie = null;

    public function __construct()
    {
        $this->config = Config::get('appcenter');
        $this->cookie = new CookieJar();
        $this->buildCookieJar();
    }

    protected function buildCookieJar()
    {
        $cookies = [];
        if ($this->config['protocol'] == 'token-as-username') {
            $cookies = [
              'username' => $this->config['username'],
              'password' => $this->config['password']
            ];
        }
        foreach ($cookies as $name => $value) {
            $set = new SetCookie();
            $set->setName($name);
            $set->setValue($value);
            $set->setSecure(true);
            $this->cookie->setCookie($set);
        }
    }

    protected function buildUserAgent()
    {
        $zbp = ZBPWrapper::getZbp();
        $app = $zbp->LoadApp('plugin', 'AppCentre');
        $currentUserAgent = 'AppValidator';
        if (isset($GLOBALS['blogversion'])) {
            $u = 'ZBlogPHP/' . $GLOBALS['blogversion'] . ' AppCentre/' . $app->modified . ' ' . $currentUserAgent;
        } else {
            $u = 'ZBlogPHP/' . substr(ZC_BLOG_VERSION, -6,
                6) . ' AppCentre/' . $app->modified . ' ' . $currentUserAgent;
        }
        return $u;
    }

    protected function newHttpClient()
    {
        return new \GuzzleHttp\Client([
          'base_uri' => $this->config['api'],
          'cookies' => $this->cookie,
          'headers' => [
            'User-Agent' => $this->buildUserAgent()
          ]
        ]);
    }

    protected function getAppServerId($appId)
    {
        // @TODO Fix Server API
        Logger::info("Getting $appId information from AppCenter...");
        $url = "?alias=$appId";
        $data = $this->newHttpClient()->request('get', $url);
        $body = (string)$data->getBody();
        $doc = new DOMDocument();
        $doc->loadHTML($body);
        $id = $doc->getElementById('inpId')->getAttribute('value');
        return $id;
    }

    protected function installAppFromRemote($appId)
    {
        $serverId = $this->getAppServerId($appId);
        $url = "?down=$serverId";
        Logger::info("Downloading $appId...");
        $data = $this->newHttpClient()->request('get', $url);
        $s = (string)$data->getBody();
        if (\App::UnPack($s)) {
            Logger::info("Extracted $appId");
            return ZBPWrapper::loadApp($appId, true);
        } else {
            Logger::warn("Extract $appId failed!");
            return false;
        }
    }
}