<?php

namespace Lemric\Sail\Tests\Command;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Lemric\Sail\Command\AddCommand;

class AddCommandTest extends KernelTestCase
{

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testCommandSuccess()
    {
        $commandTester = new CommandTester(new AddCommand(realpath(__DIR__ . '/../')));

        $commandTester->execute([
            'services' => 'redis',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Additional Sail services installed successfully.', $commandTester->getDisplay());
    }

    public function testCommandFailureDueToInvalidService()
    {
        $commandTester = new CommandTester(new AddCommand(realpath(__DIR__ . '/../')));

        $commandTester->execute([
            'services' => 'invalidService',
        ]);

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Invalid services', $commandTester->getDisplay());
    }
}