<?php

namespace Sokil\Mongo\Migrator;

use Sokil\Mongo\Migrator\Console\Exception\ConfigurationNotFound;
use Symfony\Component\Yaml\Yaml;

/**
 * Builder of Manager instance
 */
class ManagerBuilder
{
    const FORMAT_YAML = 'yaml';
    const FORMAT_PHP = 'php';

    /**
     * @var array
     */
    const ALLOWED_CONFIG_FORMATS = [
        self::FORMAT_YAML,
        self::FORMAT_PHP,
    ];

    const DEFAULT_CONFIG_FILENAME = 'mongo-migrator';

    /**
     * @param string|null $configurationPath
     *
     * @return Manager
     */
    public function build($configurationPath = null)
    {
        if (empty($configurationPath)) {
            $configurationPath = $this->locateDefaultConfigurationPath(getcwd());
        }

        // load configuration
        $config = $this->loadConfiguration($configurationPath);

        // create manager
        $manager = new Manager($config);

        return $manager;
    }

    /**
     * @param string $projectRoot
     *
     * @return string
     */
    private function locateDefaultConfigurationPath($projectRoot)
    {
        $filename = $projectRoot . '/' . self::DEFAULT_CONFIG_FILENAME;

        foreach (self::ALLOWED_CONFIG_FORMATS as $allowedConfigFormat) {
            $configurationPath = sprintf('%s.%s', $filename, $allowedConfigFormat);
            if (file_exists($configurationPath)) {
                return $configurationPath;
            }
        }

        throw new ConfigurationNotFound('Configuration not found');
    }

    /**
     * @param string $configurationPath
     *
     * @return Config
     *
     * @throws ConfigurationNotFound
     */
    private function loadConfiguration($configurationPath)
    {
        // check if config readable
        if (!is_readable($configurationPath)) {
            throw new \InvalidArgumentException('Passed configuration path is not readable');
        }

        $configurationFormat = pathinfo($configurationPath, PATHINFO_EXTENSION);

        switch ($configurationFormat) {
            case self::FORMAT_YAML:
                $configuration = Yaml::parse(file_get_contents($configurationPath));
                break;
            case self::FORMAT_PHP:
                $configuration = require($configurationPath);
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Passed configuration path must be in one of allowed formats %s',
                        implode(', ', self::ALLOWED_CONFIG_FORMATS)
                    )
                );
        }

        if (!is_array($configuration)) {
            throw new \InvalidArgumentException('Invalid config format');
        }

        // validate and normalize migrations path
        if (empty($configuration['path']['migrations'])) {
            throw new \Exception('Migrations path not specified');
        } else {
            if ($configuration['path']['migrations'][0] !== '/') {
                $configuration['path']['migrations'] = dirname($configurationPath)
                    . '/'
                    . rtrim($configuration['path']['migrations'], '/');
            }
        }

        // build configuration
        return new Config($configuration);
    }
}