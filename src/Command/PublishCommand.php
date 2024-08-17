<?php

namespace Lemric\Sail\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'env:publish', description: 'Publish the Lemric Env Docker files')]
class PublishCommand extends Command
{
    public function __construct(#[Autowire('%kernel.project_dir%')]
                                private string $projectDirectory,
                                ?string $name = null)
    {
        parent::__construct($name);
    }
    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'sail-docker']);
        $this->call('vendor:publish', ['--tag' => 'sail-database']);

        file_put_contents(
            $this->projectDirectory . '/docker-compose.yml',
            str_replace(
                [
                    './vendor/lemric/sail/runtimes/8.3',
                    './vendor/lemric/sail/runtimes/8.2',
                    './vendor/lemric/sail/runtimes/8.1',
                    './vendor/lemric/sail/runtimes/8.0',
                    './vendor/lemric/sail/database/mysql',
                    './vendor/lemric/sail/database/pgsql'
                ],
                [
                    './docker/8.3',
                    './docker/8.2',
                    './docker/8.1',
                    './docker/8.0',
                    './docker/mysql',
                    './docker/pgsql'
                ],
                file_get_contents($this->projectDirectory . '/docker-compose.yml')
            )
        );
    }
}