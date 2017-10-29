<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2017/10/29
 * Time: 9:48
 */

namespace Zsxsoft\AppValidator\Helpers;


class ServerManager
{
    use StaticInstance;
    protected $pid = '';
    protected $pidPath = '';

    public function __construct()
    {
        $this->pidPath = TempHelper::getPath('/php.pid');
        $this->loadPid();
    }

    protected function loadPid()
    {

        if (file_exists($this->pidPath)) {
            $this->pid = file_get_contents($this->pidPath);
        } else {
            $this->pid = '';
        }
    }

    protected function start()
    {
        $this->stop();
        $pipes = [];
        $proc = proc_open('php -S ' . SERVER_LISTEN_ADDRESS,
            [
                0 => ["pipe", "r"],
                1 => ['file', TempHelper::getPath('/server-output.txt'), 'w'],
                2 => ['file', TempHelper::getPath('/server-error.txt'), 'w'],
            ],
            $pipes, TempHelper::getPath('/web'), NULL,
            [
                'bypass_shell' => true
            ]
        );
        if (!is_resource($proc)) {
            Logger::error('Start PHP Server failed!');
            exit;
        }
        $status = proc_get_status($proc);
        $pid = $status['pid'];
        Logger::info("Started PHP Server(PID=${pid}) at " . SERVER_LISTEN_ADDRESS);
        $this->pid = $pid;
        file_put_contents($this->pidPath, $pid);
    }

    protected function stop()
    {
        $this->loadPid();
        if ($this->pid == '') return;
        if (function_exists('posix_kill')) {
            posix_kill($this->pid, SIGTERM);
        } else if (DIRECTORY_SEPARATOR === '\\') {
            `taskkill.exe /pid {$this->pid} /f`;
        } else {
            `kill {$this->pid}`;
        }

        @unlink($this->pidPath);
    }

}