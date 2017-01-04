<?php

namespace Sokil\Mongo\Migrator;

use Symfony\Component\Yaml\Yaml;
use Sokil\Mongo\Migrator\Console\Command;

class ManagerMock extends \Sokil\Mongo\Migrator\Manager
{
    public function resetCollection($environment)
    {
        $this->getLogCollection($environment)->delete();
    }
}

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    private $manager;
    
    public function setUp()
    {
        try {
            $configFile = __DIR__ . '/' . Command::CONFIG_FILENAME . '.yaml';
            $config = Yaml::parse(file_get_contents($configFile));

            // replace dsn with env value
            $envDSN = getenv('PHPMONGO_DSN');
            if ($envDSN) {
                foreach (array_keys($config['environments']) as $environment) {
                    $config['environments'][$environment]['dsn'] = $envDSN;
                }
            }

            // init manager
            $this->manager = new ManagerMock( new Config($config), __DIR__);
            $this->manager->resetCollection('staging');
        } catch (\Exception $e) {
            var_export($e);
            throw $e;
        }
        
    }
    
    public function tearDown()
    {
        $this->manager->resetCollection('staging');
    }
    
    public function testGetAvailableRevisions()
    {
        $availableRevisions = $this->manager->getAvailableRevisions();
        
        $this->assertInternalType('array', $availableRevisions);
        
        $revision = current($availableRevisions);
        $this->assertInstanceOf('\Sokil\Mongo\Migrator\Revision', $revision);
        
        $this->assertEquals('20140531201029', $revision->getId());
        $this->assertEquals('Test5', $revision->getName());
        $this->assertEquals('20140531201029_Test5.php', $revision->getFilename());
    }

    public function testGetClient()
    {
        $reflectionClass = new \ReflectionClass($this->manager);
        $method = $reflectionClass->getMethod('getClient');
        $method->setAccessible(true);

        $devClient = $method->invoke($this->manager, 'development');
        $this->assertInstanceof('\Sokil\Mongo\Client', $devClient);
        $this->assertEquals('test', $devClient->getCurrentDatabaseName());

        $stagingClient = $method->invoke($this->manager, 'staging');
        $this->assertInstanceof('\Sokil\Mongo\Client', $stagingClient);
        $this->assertEquals('staging_db', $stagingClient->getCurrentDatabaseName());
    }
    
    public function testMigrate()
    {
        $this->manager->resetCollection('staging');
        
        $this->manager->migrate('20140531201024', 'staging');
        
        $this->assertTrue($this->manager->isRevisionApplied('20140531201024', 'staging'));
        $this->assertTrue($this->manager->isRevisionApplied('20140531201019', 'staging'));
        
        $this->assertFalse($this->manager->isRevisionApplied('20140531201027', 'staging'));
    }
    
    public function testRollback()
    {
        $this->manager->migrate(null, 'staging');
        
        $this->manager->rollback('20140531201024', 'staging');
        
        $this->assertTrue($this->manager->isRevisionApplied('20140531201019', 'staging'));
        $this->assertTrue($this->manager->isRevisionApplied('20140531201024', 'staging'));
        
        $this->assertFalse($this->manager->isRevisionApplied('20140531201025', 'staging'));
        $this->assertFalse($this->manager->isRevisionApplied('20140531201027', 'staging'));
    }
}