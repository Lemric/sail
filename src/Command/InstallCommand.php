<?php
namespace Lemric\Sail\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'sail:install', description: 'Install Lemric Env\'s default Docker Compose file')]
class InstallCommand extends Command
{
    use InteractsWithDockerComposeServices;

    public function __construct(#[Autowire('%kernel.project_dir%')]
                                private string $projectDirectory,
                                ?string $name = null)
    {
        parent::__construct($name);
    }
    protected function configure(): void
    {
        $this
            ->addOption('with', 'w', InputOption::VALUE_OPTIONAL, 'The services that should be included in the installation')
            ->addOption('devcontainer', null, InputOption::VALUE_OPTIONAL, 'Create a .devcontainer configuration directory')
        ;
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(!empty($input->getOption('with'))) {
            $services = $input->getOption('with') == 'none' ? [] : explode(',', $input->getOption('with'));
        } elseif (!$input->isInteractive()) {
            $services = $this->defaultServices;
        } else {
            $services = $this->gatherServicesInteractively($input, $output);
        }

        if ($invalidServices = array_diff($services, $this->services)) {
            $output->writeln(sprintf('<error>Invalid services [%s].</error>', implode(',', $invalidServices)));

            return Command::FAILURE;
        }

        $this->buildDockerCompose($services, $output);
        $this->replaceEnvVariables($services);
        $this->configurePhpUnit();

        if ($input->hasOption('devcontainer')) {
            $this->installDevContainer();
        }

        $this->prepareInstallation($services, $output);

        $output->writeln('');
        $output->writeln('<info>Env scaffolding installed successfully. You may run your Docker containers using Env\'s "up" command.</info>');

        $output->writeln('<fg=gray>➜</> <options=bold>./vendor/bin/lemric up</>');

        if (in_array('mysql', $services) ||
            in_array('mariadb', $services) ||
            in_array('pgsql', $services)) {
            $output->writeln('<warn>A database service was installed. Run "artisan migrate" to prepare your database:</warn>');

            $output->writeln('<fg=gray>➜</> <options=bold>bin/console doctrine:migrations:migrate</>');
        }

        $output->writeln('');

        return Command::SUCCESS;
    }
}