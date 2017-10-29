<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 14:17
 */

namespace Zsxsoft\AppValidator\Tasks;


use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt;
use Zsxsoft\AppValidator\Helpers\Logger;
use Zsxsoft\AppValidator\Helpers\PathHelper;
use Zsxsoft\AppValidator\Helpers\ZBPHelper;
use Zsxsoft\AppValidator\Tasks\StaticCodeScanner\ErrorOutputter;
use Zsxsoft\AppValidator\Tasks\StaticCodeScanner\NameChecker;
use Zsxsoft\AppValidator\Wrappers\ZBPWrapper;

class ScanStaticCode
{
    private $file = "";
    private $path = "";

    /**
     * Check unsafe functions
     */
    public function checkFunctions()
    {
        $outputter = new ErrorOutputter($this->path);
        $checker = new NameChecker();
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver);
        $traverser->addVisitor($checker);
        $nodeDumper = new NodeDumper;
        try {
            $stmts = $parser->parse($this->file);
            $stmts = $traverser->traverse($stmts);
            foreach ($checker->ret as $item) {
                $outputter->{$item['type'] . '_'}($item);
            }
        } catch (\PhpParser\Error $e) {
            Logger::error("Parse {$this->path} Error.");
            Logger::error($e->getMessage());
        }
    }

    /**
     * Check Order By Rand
     */
    public function checkOrderByRand()
    {
        $regex = "/[\"']rand\(\)[\"'][ \t]*?\=\>[\"'][ \t]*?[\"']|ORDER[ \t]*BY[\t ]*rand\(/i";
        $matches = null;
        if (preg_match($regex, $this->file)) {
            Logger::warning('Maybe using rand() in MySQL in ' . $this->path);
            Logger::warning('You should remove it.');
        }
    }

    /**
     * Run Checker
     * @param string $path
     */
    public function runChecker($filePath)
    {
        // Logger::info("Scanning $filePath");
        $this->path = $filePath;
        $this->file = file_get_contents($this->path);
        $this->checkOrderByRand();
        $this->checkFunctions();
    }

    /**
     * Run
     */
    public function run()
    {
        $zbp = ZBPWrapper::getZbp();
        $app = ZBPWrapper::getApp();
        $pluginDir = ZBPHelper::getPath() . '/zb_users/' . $app->type . '/' . $app->id . '/';
        foreach (PathHelper::scanDirectory($pluginDir) as $index => $value) {
            $this->runChecker(PathHelper::getAbsoluteFilename($value));
        }
        if ($app->type === 'theme') {
            $compiledDir = ZBPHelper::getPath() . '/zb_users/cache/compiled/' . $app->id . '/';
            if (!is_dir($compiledDir)) return;
            foreach (PathHelper::scanDirectory($compiledDir) as $index => $value) {
                $this->runChecker(PathHelper::getAbsoluteFilename($value));
            }
        }
    }
}
