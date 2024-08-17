<?php

namespace Lemric\Sail\Command;

use Symfony\Component\Console\Output\OutputInterface;
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
    protected array $defaultServices = ['mysql', 'redis', 'selenium', 'mailpit'];

    /**
     * Gather the desired Sail services using an interactive prompt.
     *
     * @return array
     */
    protected function gatherServicesInteractively(): array
    {
        if (function_exists('\Laravel\Prompts\multiselect')) {
            return \Laravel\Prompts\multiselect(
                label: 'Which services would you like to install?',
                options: $this->services,
                default: ['mysql'],
            );
        }

        return $this->choice('Which services would you like to install?', $this->services, 0, null, true);
    }

    /**
     * Build the Docker Compose file.
     *
     * @param  array  $services
     * @return void
     */
    protected function buildDockerCompose(array $services, OutputInterface $output): void
    {
        $composePath = __DIR__ . '/docker-compose.yml';
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

        // Add the services to the docker-compose.yml...
        $services = array_filter($services, function ($service) use ($compose) {
            return ! array_key_exists($service, $compose['services'] ?? []);
        });

        $services = array_map(function ($service) use (&$compose) {
            $compose['services'][$service] = Yaml::parseFile(__DIR__ . "/../../stubs/{$service}.stub")[$service];
        }, $services);

        $services = array_filter($services,function ($service) {
            return in_array($service, ['mysql', 'pgsql', 'mariadb', 'redis', 'meilisearch', 'typesense', 'minio']);
        });
        $services = array_filter($services,function ($service) use ($compose) {
            return ! array_key_exists($service, $compose['volumes'] ?? []);
        });

        $services = array_map(function ($service) use (&$compose) {
            $compose['volumes']["sail-{$service}"] = ['driver' => 'local'];
        }, $services);

        // If the list of volumes is empty, we can remove it...
        if (empty($compose['volumes'])) {
            unset($compose['volumes']);
        }

        // Replace Selenium with ARM base container on Apple Silicon...
        if (in_array('selenium', $services) && in_array(php_uname('m'), ['arm64', 'aarch64'])) {
            $compose['services']['selenium']['image'] = 'seleniarm/standalone-chromium';
        }

        file_put_contents($this->projectDirectory . '/docker-compose.yml', Yaml::dump($compose, Yaml::DUMP_OBJECT_AS_MAP));
    }

    /**
     * Replace the Host environment variables in the app's .env file.
     *
     * @param  array  $services
     * @return void
     */
    protected function replaceEnvVariables(array $services): void
    {
        $environment = file_get_contents(realpath($this->projectDirectory . '/.env'));

        if (in_array('mysql', $services) ||
            in_array('mariadb', $services) ||
            in_array('pgsql', $services)) {
            $defaults = [
                '# DB_HOST=127.0.0.1',
                '# DB_PORT=3306',
                '# DB_DATABASE=symfony',
                '# DB_USERNAME=root',
                '# DB_PASSWORD=',
            ];

            foreach ($defaults as $default) {
                $environment = str_replace($default, substr($default, 2), $environment);
            }
        }

        if (in_array('mysql', $services)) {
            $environment = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $environment);
            $environment = str_replace('DB_HOST=127.0.0.1', "DB_HOST=mysql", $environment);
        }elseif (in_array('pgsql', $services)) {
            $environment = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=pgsql', $environment);
            $environment = str_replace('DB_HOST=127.0.0.1', "DB_HOST=pgsql", $environment);
            $environment = str_replace('DB_PORT=3306', "DB_PORT=5432", $environment);
        } elseif (in_array('mariadb', $services)) {
            if ($this->symfony->config->has('database.connections.mariadb')) {
                $environment = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mariadb', $environment);
            }

            $environment = str_replace('DB_HOST=127.0.0.1', "DB_HOST=mariadb", $environment);
        }

        $environment = str_replace('DB_USERNAME=root', "DB_USERNAME=sail", $environment);
        $environment = preg_replace("/DB_PASSWORD=(.*)/", "DB_PASSWORD=password", $environment);

        if (in_array('memcached', $services)) {
            $environment = str_replace('MEMCACHED_HOST=127.0.0.1', 'MEMCACHED_HOST=memcached', $environment);
        }

        if (in_array('redis', $services)) {
            $environment = str_replace('REDIS_HOST=127.0.0.1', 'REDIS_HOST=redis', $environment);
        }

        if (in_array('meilisearch', $services)) {
            $environment .= "\nSCOUT_DRIVER=meilisearch";
            $environment .= "\nMEILISEARCH_HOST=http://meilisearch:7700\n";
            $environment .= "\nMEILISEARCH_NO_ANALYTICS=false\n";
        }

        if (in_array('typesense', $services)) {
            $environment .= "\nSCOUT_DRIVER=typesense";
            $environment .= "\nTYPESENSE_HOST=typesense";
            $environment .= "\nTYPESENSE_PORT=8108";
            $environment .= "\nTYPESENSE_PROTOCOL=http";
            $environment .= "\nTYPESENSE_API_KEY=xyz\n";
        }

        if (in_array('soketi', $services)) {
            $environment = preg_replace("/^BROADCAST_DRIVER=(.*)/m", "BROADCAST_DRIVER=pusher", $environment);
            $environment = preg_replace("/^PUSHER_APP_ID=(.*)/m", "PUSHER_APP_ID=app-id", $environment);
            $environment = preg_replace("/^PUSHER_APP_KEY=(.*)/m", "PUSHER_APP_KEY=app-key", $environment);
            $environment = preg_replace("/^PUSHER_APP_SECRET=(.*)/m", "PUSHER_APP_SECRET=app-secret", $environment);
            $environment = preg_replace("/^PUSHER_HOST=(.*)/m", "PUSHER_HOST=soketi", $environment);
            $environment = preg_replace("/^PUSHER_PORT=(.*)/m", "PUSHER_PORT=6001", $environment);
            $environment = preg_replace("/^PUSHER_SCHEME=(.*)/m", "PUSHER_SCHEME=http", $environment);
            $environment = preg_replace("/^VITE_PUSHER_HOST=(.*)/m", "VITE_PUSHER_HOST=localhost", $environment);
        }

        if (in_array('mailpit', $services)) {
            $environment = preg_replace("/^MAIL_MAILER=(.*)/m", "MAIL_MAILER=smtp", $environment);
            $environment = preg_replace("/^MAIL_HOST=(.*)/m", "MAIL_HOST=mailpit", $environment);
            $environment = preg_replace("/^MAIL_PORT=(.*)/m", "MAIL_PORT=1025", $environment);
        }

        file_put_contents(realpath($this->projectDirectory .'/.env'), $environment);
    }

    /**
     * Configure PHPUnit to use the dedicated testing database.
     *
     * @param string $this->projectDirectory
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