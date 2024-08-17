<?php

namespace Lemric\Sail\Tests\Command;

use Lemric\Sail\Command\AddCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Lemric\Sail\Command\InstallCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallCommandTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testCommandSuccessWithServices()
    {
        $commandTester = new CommandTester(new InstallCommand(realpath(__DIR__ . '/../')));

        $commandTester->execute([
            '--with' => 'redis',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Env scaffolding installed successfully.', $commandTester->getDisplay());
    }

    public function testCommandFailureDueToInvalidService()
    {
        $commandTester = new CommandTester(new InstallCommand(realpath(__DIR__ . '/../')));

        $commandTester->execute([
            '--with' => 'invalidService',
        ]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Invalid services [invalidService].', $commandTester->getDisplay());
    }

    public function testCommandWithDevContainerOption()
    {
        $commandTester = new CommandTester(new InstallCommand(realpath(__DIR__ . '/../')));

        $commandTester->execute([
            '--with' => 'redis',
            '--devcontainer' => true,
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
