<?php

namespace Lemric\Sail\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

trait InteractsWithDockerComposeServices
{
    /**
     * The available services that may be installed.
     *
     * @var array<string>
     */
    protected array $services = [
        'mysql',
        'pgsql',
        'mariadb',
        'redis',
        'memcached',
        'meilisearch',
        'typesense',
        'minio',
        'mailpit',
        'selenium',
        'soketi',
    ];

    /**
     * The default services used when the user chooses non-interactive mode.
     *
     * @var string[]
     */
    protected array $defaultServices = ['pgsql', 'redis', 'mailpit'];

    /**
     * Gather the desired Sail services using an interactive prompt.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function gatherServicesInteractively(InputInterface $input, OutputInterface $output): array
    {
        $composePath = $this->projectDirectory . '/docker-compose.yml';
        $compose = file_exists($composePath)
            ? Yaml::parseFile($composePath)
            : ['services' => ['pgsql' => true]];
        $default = array_filter(array_keys($compose['services']), function($service) {
            return $service !== 'symfony.test';
        });
        if (function_exists('\Laravel\Prompts\multiselect')) {
            return \Laravel\Prompts\multiselect(
                label: 'Which services would you like to install?',
                options: $this->services,
                default: $default,
            );
        }

        $io = new SymfonyStyle($input, $output);
        return $io->choice('Which services would you like to install?', $this->services, $default);
    }

    /**
     * Build the Docker Compose file.
     *
     * @param array $services
     * @param OutputInterface $output
     * @return void
     */
    protected function buildDockerCompose(array $services, OutputInterface $output): void
    {
        $composePath = $this->projectDirectory . '/docker-compose.yml';
        $compose = file_exists($composePath)
            ? Yaml::parseFile($composePath)
            : Yaml::parse(file_get_contents(__DIR__ . '/../../stubs/docker-compose.stub'));

        // Prepare the installation of the "mariadb-client" package if the MariaDB service is used...
        if (in_array('mariadb', $services)) {
            $compose['services']['symfony.test']['build']['args']['MYSQL_CLIENT'] = 'mariadb-client';
        }

        // Adds the new services as dependencies of the symfony.test service...
        if (! array_key_exists('symfony.test', $compose['services'])) {
            $output->writeln(sprintf('<warn>Couldn\'t find the symfony.test service. Make sure you add [%s] to the depends_on config.</warn>', implode(',', $services)));
        } else {
            $dependsOn = $compose['services']['symfony.test']['depends_on'] ?? [];
            $dependsOn = array_merge($dependsOn, $services);
            $dependsOn = array_unique($dependsOn, SORT_REGULAR);
            $dependsOn = array_values($dependsOn);
            $compose['services']['symfony.test']['depends_on'] = $dependsOn;
        }

        // Update the dependencies if the MariaDB service is used...
        if (in_array('mariadb', $services)) {
            $compose['services']['symfony.test']['depends_on'] = array_map(function ($dependedItem) {
                return $dependedItem;
            }, $compose['services']['symfony.test']['depends_on']);
        }

        $newServices = $services;
        // Add the services to the docker-compose.yml...
        $newServices = array_filter($newServices, function ($service) use ($compose) {
            return ! array_key_exists($service, $compose['services'] ?? []);
        });

        array_map(function ($service) use (&$compose) {
            $compose['services'][$service] = Yaml::parseFile(__DIR__ . "/../../stubs/{$service}.stub")[$service];
        }, $newServices);

        $newServices = $services;
        $newServices = array_filter($newServices,function ($service) {
            return in_array($service, ['mysql', 'pgsql', 'mariadb', 'redis', 'meilisearch', 'typesense', 'minio']);
        });
        $newServices = array_filter($newServices,function ($service) use ($compose) {
            return ! array_key_exists($service, $compose['volumes'] ?? []);
        });

        array_map(function ($service) use (&$compose) {
            $compose['volumes']["sail-{$service}"] = ['driver' => 'local'];
        }, $newServices);

        // If the list of volumes is empty, we can remove it...
        if (empty($compose['volumes'])) {
            unset($compose['volumes']);
        }

        // Replace Selenium with ARM base container on Apple Silicon...
        if (in_array('selenium', $services) && in_array(php_uname('m'), ['arm64', 'aarch64'])) {
            $compose['services']['selenium']['image'] = 'seleniarm/standalone-chromium';
        }

        $compose = $this->replaceEnvVariables($compose);

        file_put_contents($this->projectDirectory . '/docker-compose.yml', Yaml::dump($compose, Yaml::DUMP_OBJECT_AS_MAP));
    }

    protected function updateDataString(string $contents, string $data): ?string
    {
        $pieces = explode("\n", trim($data));
        $startMark = trim(reset($pieces));
        $endMark = trim(end($pieces));

        if (!str_contains($contents, $startMark) || !str_contains($contents, $endMark)) {
            return null;
        }

        $pattern = '/'.preg_quote($startMark, '/').'.*?'.preg_quote($endMark, '/').'/s';

        return preg_replace($pattern, trim($data), $contents);
    }

    /**
     * Replace the Host environment variables in the app's .env file.
     *
     * @param array $compose
     * @return array
     */
    protected function replaceEnvVariables(array $compose): array
    {
        foreach ($compose['services'] as $name => $service) {
            switch ($name) {
                case 'mysql':
                    $compose['services']['symfony.test']['environment']['DATABASE_URL'] =
                        sprintf('mysql://%s:%s@mysql:3306/%s?serverVersion=8.0.32&charset=utf8mb4',
                            $service['environment']['MYSQL_USER'],
                            $service['environment']['MYSQL_PASSWORD'],
                            $service['environment']['MYSQL_DATABASE']
                        );
                    break;
                case 'mariadb':
                    $compose['services']['symfony.test']['environment']['DATABASE_URL'] =
                        sprintf('mysql://%s:%s@mariadb:3306/%s?serverVersion=10.11.2-MariaDB&charset=utf8mb4',
                            $service['environment']['MYSQL_USER'],
                            $service['environment']['MYSQL_PASSWORD'],
                            $service['environment']['MYSQL_DATABASE']
                        );
                    break;
                case 'pgsql':
                    $compose['services']['symfony.test']['environment']['DATABASE_URL'] =
                        sprintf('postgresql://%s:%s@pgsql:5432/%s/app?serverVersion=16&charset=utf8',
                        $service['environment']['POSTGRES_USER'],
                        $service['environment']['POSTGRES_PASSWORD'],
                        $service['environment']['POSTGRES_DB'],
                    );
                    break;
                case 'redis':
                    $compose['services']['symfony.test']['environment']['REDIS_DSN'] = 'redis://redis';
                    break;
                case 'memcached':
                    $compose['services']['symfony.test']['environment']['MEMCACHED_HOST'] = 'memcached';
                    break;
                case 'meilisearch':
                    $compose['services']['symfony.test']['environment']['SCOUT_DRIVER'] = 'meilisearch';
                    $compose['services']['symfony.test']['environment']['MEILISEARCH_HOST'] = 'http://meilisearch:7700';
                    $compose['services']['symfony.test']['environment']['MEILISEARCH_NO_ANALYTICS'] = 'false';

                    break;
                case 'typesense':
                    $compose['services']['symfony.test']['environment']['SCOUT_DRIVER'] = 'typesense';
                    $compose['services']['symfony.test']['environment']['TYPESENSE_HOST'] = 'typesense';
                    $compose['services']['symfony.test']['environment']['TYPESENSE_PORT'] = '8108';
                    $compose['services']['symfony.test']['environment']['TYPESENSE_PROTOCOL'] = 'http';
                    $compose['services']['symfony.test']['environment']['TYPESENSE_API_KEY'] = 'xyz';

                    break;
                case 'soketi':
                    $compose['services']['symfony.test']['environment']['BROADCAST_DRIVER'] = 'pusher';
                    $compose['services']['symfony.test']['environment']['PUSHER_APP_ID'] = 'app-id';
                    $compose['services']['symfony.test']['environment']['PUSHER_APP_KEY'] = 'app-key';
                    $compose['services']['symfony.test']['environment']['PUSHER_APP_SECRET'] = 'app-secret';
                    $compose['services']['symfony.test']['environment']['PUSHER_HOST'] = 6001;
                    $compose['services']['symfony.test']['environment']['PUSHER_SCHEME'] = 'http';
                    $compose['services']['symfony.test']['environment']['VITE_PUSHER_HOST'] = 'localhost';

                    break;
                case 'mailpit':
                    $compose['services']['symfony.test']['environment']['MAILER_DSN'] = 'smtp://mailpit:1025';
                    break;
            }
        }

        return $compose;
    }

    /**
     * Configure PHPUnit to use the dedicated testing database.
     *
     * @return void
     */
    protected function configurePhpUnit(): void
    {
        if (! file_exists($path = realpath($this->projectDirectory . '/phpunit.xml'))) {
            $path = realpath($this->projectDirectory . '/phpunit.xml.dist');

            if (! file_exists($path)) {
                return;
            }
        }

        $phpunit = file_get_contents($path);

        $phpunit = preg_replace('/^.*DB_CONNECTION.*\n/m', '', $phpunit);
        $phpunit = str_replace('<!-- <env name="DB_DATABASE" value=":memory:"/> -->', '<env name="DB_DATABASE" value="testing"/>', $phpunit);

        file_put_contents($this->projectDirectory . '/phpunit.xml', $phpunit);
    }

    /**
     * Install the devcontainer.json configuration file.
     *
     * @return void
     */
    protected function installDevContainer(): void
    {
        if (! is_dir($this->projectDirectory . '/.devcontainer')) {
            mkdir($this->projectDirectory . '/.devcontainer', 0755, true);
        }

        file_put_contents(
            $this->projectDirectory . '/.devcontainer/devcontainer.json',
            file_get_contents(__DIR__.'/../../stubs/devcontainer.stub')
        );
        file_put_contents(
            $this->projectDirectory . '/.devcontainer/unit.json',
            file_get_contents(__DIR__.'/../../stubs/unit.stub')
        );

        $environment = file_get_contents($this->projectDirectory . '/.env');

        $environment .= "\nWWWGROUP=1000";
        $environment .= "\nWWWUSER=1000\n";

        file_put_contents($this->projectDirectory . '/.env', $environment);
    }

    /**
     * Prepare the installation by pulling and building any necessary images.
     *
     * @param array $services
     * @param OutputInterface $output
     * @return void
     */
    protected function prepareInstallation(array $services, OutputInterface $output): void
    {
        // Ensure docker is installed...
        if ($this->runCommands(['docker info > /dev/null 2>&1'], $output) !== 0) {
            return;
        }

        if (count($services) > 0) {
            $this->runCommands([
                './vendor/bin/lemric pull '.implode(' ', $services),
            ], $output);
        }

        $this->runCommands([
            './vendor/bin/lemric build',
        ], $output);
    }

    /**
     * Run the given commands.
     *
     * @param array $commands
     * @param OutputInterface $output
     * @return int
     */
    protected function runCommands(array $commands, OutputInterface $output): int
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (\RuntimeException $e) {
                $output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        return $process->run(function ($type, $line) use($output) {
            $output->write('    '.$line);
        });
    }
}