<?php

use Valet\PhpFpm;
use function Valet\user;
use function Valet\resolve;
use Illuminate\Container\Container;

class PhpFpmTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['SUDO_USER'] = user();

        Container::setInstance(new Container);
    }


    public function tearDown()
    {
        exec('rm -rf '.__DIR__.'/output');
        mkdir(__DIR__.'/output');
        touch(__DIR__.'/output/.gitkeep');

        Mockery::close();
    }

    public function test_fpm_is_configured_with_the_correct_user_group_and_port()
    {
        copy(__DIR__.'/files/fpm.conf', __DIR__.'/output/fpm.conf');
        mkdir(__DIR__.'/output/conf.d');
        copy(__DIR__.'/files/php-memory-limits.ini', __DIR__.'/output/conf.d/php-memory-limits.ini');
        resolve(StubForUpdatingFpmConfigFiles::class)->updateConfiguration();
        $contents = file_get_contents(__DIR__.'/output/fpm.conf');
        $this->assertContains(sprintf("\nuser = %s", user()), $contents);
        $this->assertContains("\ngroup = staff", $contents);
        $this->assertContains("\nlisten = ".VALET_HOME_PATH."/valet.sock", $contents);
    }
}


class StubForUpdatingFpmConfigFiles extends PhpFpm
{
    function fpmConfigPath()
    {
        return __DIR__.'/output/fpm.conf';
    }
}
