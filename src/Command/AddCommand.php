<?php

namespace Lemric\Sail\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'sail:add', description: 'Add a service to an existing installation')]
class AddCommand extends Command
{
    use InteractsWithDockerComposeServices;
    public function __construct(#[Autowire('%kernel.project_dir%')] private string $projectDirectory, ?string $name = null)
    {
        parent::__construct($name);
    }
    protected function configure(): void
    {
        $this
            ->addArgument('services', InputArgument::OPTIONAL, 'The services that should be added', [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if($input->hasArgument('services')) {
            $services = $input->getArgument('services') == 'none' ? [] : explode(',', $input->getArgument('services'));
        } else if(!$input->isInteractive()) {
            $services = $this->defaultServices;
        } else {
            $services = $this->gatherServicesInteractively();
        }

        if ($invalidServices = array_diff($services, $this->services)) {
            $output->writeln(sprintf('<error>Invalid services [%s]</error>', implode(',', $invalidServices)));
            return Command::FAILURE;
        }

        $this->buildDockerCompose($services, $output);
        $this->replaceEnvVariables($services);
        $this->configurePhpUnit();

        $this->prepareInstallation($services, $output);

        $output->writeln('');
        $output->writeln('<info>Additional Sail services installed successfully.</info>');

        return Command::SUCCESS;
    }
}