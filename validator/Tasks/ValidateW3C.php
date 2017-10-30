<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 15:07
 */

namespace Zsxsoft\AppValidator\Tasks;

use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Wrappers\Config;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;
use Zsxsoft\W3CValidator\W3CValidator;

class ValidateW3C
{
    public function run()
    {
        $zbp = ZBPWrapper::getZbp();
        $app = ZBPWrapper::getApp();
        $urls = Config::get('validateUrls');

        foreach ($urls as $url) {
            $url = Config::get('host') . $url;
            Logger::info("Validating $url");
            $validator = new W3CValidator();
            // $ret = $validator->fileName($url)->run();
            $ret = $validator->data(file_get_contents($url))->run();
            $this->outputError($ret['error']->messages);
        }

    }

    public function outputError($errors)
    {
        foreach ($errors as $item) {
            if ($this->ignoreError($item)) continue;
            $type = $item->type;
            Logger::$type("{$item->lastLine}.{$item->firstColumn}-{$item->lastLine}.{$item->lastColumn}: {$item->message}");
            Logger::$type($item->extract);
        }
    }

    public function ignoreError($item)
    {
        $message = $item->message;

        // <h5 class="post-tags"></h5>
        if ($message === 'Empty heading.') return true;

        // "This document appears to be written in 希伯来语 but the “html” start tag has “lang="zh-Hans"”. Consider using “lang="he"” (or variant) instead."
        // Ignore <html lang>
        if (preg_match('/This document appears to be written in/i', $message)) return true;

        // Attribute “_src” not allowed on element “a” at this point.
        if (preg_match('/Attribute .*? not allowed on element .*? at this point/i', $message)) return true;

        // An “img” element must have an “alt” attribute, except under certain conditions. For details, consult guidance on providing text alternatives for images.
        if (preg_match('/An.*img.*element must have a.*alt.*attribute/i', $message)) return true;

        // Section lacks heading.
        if (preg_match('/Section lacks heading./', $message)) return true;

        // The “itemprop” attribute was specified, but the element is not a property of any item.
        if (preg_match('/but the element is not a property of any item./', $message)) return true;

        // A “meta” element with an “http-equiv” attribute whose value is “X-UA-Compatible” must have a “content” attribute with the value “IE=edge”.
        if (preg_match('/http-equiv.*?X-UA-Compatible.*?IE=edge/i', $message)) return true;

        // Bad value “Cache-Control” for attribute “http-equiv” on element “meta”
        if (preg_match('/Cache-Control.*?http-equiv.*?meta/i', $message)) return true;

        // The literal did not satisfy the time-datetime format.
        if (preg_match('/The literal did not satisfy the time-datetime format./i', $message)) return true;

        return false;
    }
}