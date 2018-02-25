<?php

namespace Amp\Ssh\Tests;

use Amp\Delayed;
use Amp\Loop;
use Amp\Ssh\Authentication\UsernamePassword;
use function Amp\Ssh\connect;
use Amp\Ssh\Process;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    public function testProcess()
    {
        Loop::run(function () {
            $authentication = new UsernamePassword('root', 'root');
            $sshResource = yield connect('127.0.0.1:2222', $authentication);

            $process = new Process($sshResource, 'echo foo');

            yield $process->start();

            self::assertTrue($process->isRunning());

            $exitCode = yield $process->join();
            $output = '';

            while ($read = yield $process->getStdout()->read()) {
                $output .= $read;
            }

            self::assertFalse($process->isRunning());
            self::assertEquals("foo\n", $output);
            self::assertEquals(0, $exitCode);

            yield $sshResource->close();
        });
    }
}