<?php

namespace Sokil\Mongo\Migrator;

class Manager
{
    /**
     *
     * @var \Sokil\Mongo\Migrator\Config
     */
    private $_config;
    
    /**
     *
     * @var \Sokil\Mongo\Client
     */
    private $_client;
    
    /**
     *
     * @var \Sokil\Mongo\Collection
     */
    private $_logCollection;
    
    private $_appliedRevisions;
    
    /**
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $_eventDispatcher;
    
    public function __construct(Config $config)
    {
        $this->_config = $config;
        
        $this->_eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher;
    }
    
    /**
     * 
     * @return \Sokil\Mongo\Client
     */
    private function getClient($environment)
    {
        if(empty($this->_client[$environment])) {
            $this->_client[$environment] = new \Sokil\Mongo\Client($this->_config->getDsn($environment));
            
            $this->_client[$environment]->useDatabase($this->_config->getDefaultDatabaseName());
        }
        
        return $this->_client[$environment];
    }
    
    public function getAvailableMigrations()
    {
        $list = array();
        foreach(new \DirectoryIterator($this->_config->getMigrationsDir()) as $file) {
            if(!$file->isFile()) {
                continue;
            }
            
            list($revision, $className) = explode('_', $file->getBasename('.php'));
            
            $list[$revision] = array(
                'revision'  => $revision,
                'className' => $className,
                'fileName'  => $file->getFilename(),
            );
            
            krsort($list);
        }
        
        return $list;
    }
    
    private function getLogCollection($environment)
    {
        if($this->_logCollection) {
            return $this->_logCollection;
        }
        
        $databaseName = $this->_config->getLogDatabaseName($environment);
        $collectionName = $this->_config->getLogCollectionName($environment);
        
        $this->_logCollection = $this
            ->getClient($environment)
            ->getDatabase($databaseName)
            ->getCollection($collectionName);
        
        return $this->_logCollection;
    }
    
    private function logUp($revision, $environment)
    {
        $this->getLogCollection($environment)->createDocument(array(
            'revision'  => $revision,
            'date'      => new \MongoDate, 
        ))->save();
        
        return $this;
    }
    
    private function logDown($revision, $environment)
    {
        $collection = $this->getLogCollection($environment);
        $collection->deleteDocuments($collection->expression()->where('revision', $revision));
        
        return $this;
    }
    
    private function getAppliedRevisions($environment)
    {
        if(isset($this->_appliedRevisions[$environment])) {
            return $this->_appliedRevisions[$environment];
        }
        
        $this->_appliedRevisions[$environment] = array_values($this
            ->getLogCollection($environment)
            ->find()
            ->sort(array('revision' => 1))
            ->map(function($document) {
                return $document->revision;
            }));
            
        return $this->_appliedRevisions[$environment];
    }
    
    public function isRevisionApplied($revision, $environment)
    {
        return in_array($revision, $this->getAppliedRevisions($environment));
    }
    
    private function getLatestAppliedRevision($environment)
    {
        return end($this->getAppliedRevisions($environment));
    }
    
    private function executeMigration($revision, $environment, $direction)
    {
        $this->_eventDispatcher->dispatch('start');
        
        // get last applied migration
        $latestRevision = $this->getLatestAppliedRevision($environment);
        
        // get list of migrations
        $availableMigrations = $this->getAvailableMigrations();
        
        // execute
        if($direction === 1) {
            $this->_eventDispatcher->dispatch('before_migrate');
            
            ksort($availableMigrations);

            foreach($availableMigrations as $migrationMeta) {
                
                if($migrationMeta['revision'] <= $latestRevision) {
                    continue;
                }
                
                $this->_eventDispatcher->dispatch('before_migrate_revision');

                require_once $this->_config->getMigrationsDir() . '/' . $migrationMeta['fileName'];
                $migration = new $migrationMeta['className']($this->getClient($environment));
                $migration->up();
                
                $this->logUp($migrationMeta['revision'], $environment);
                
                $this->_eventDispatcher->dispatch('migrate_revision');
                
                if($revision && in_array($revision, array($migrationMeta['revision'], $migrationMeta['className']))) {
                    break;
                }
            }
            
            $this->_eventDispatcher->dispatch('migrate');
        } else {
            
            $this->_eventDispatcher->dispatch('before_rollback');
            
            // check if nothing to revert
            if(!$latestRevision) {
                return;
            }
            
            krsort($availableMigrations);

            foreach($availableMigrations as $migrationMeta) {

                if($migrationMeta['revision'] > $latestRevision) {
                    continue;
                }
                
                $this->_eventDispatcher->dispatch('before_rollback_revision');

                require_once $this->_config->getMigrationsDir() . '/' . $migrationMeta['fileName'];
                $migration = new $migrationMeta['className']($this->getClient($environment));
                $migration->down();
                
                $this->logDown($migrationMeta['revision'], $environment);
                
                $this->_eventDispatcher->dispatch('rollback_revision');
                
                if(!$revision || in_array($revision, array($migrationMeta['revision'], $migrationMeta['className']))) {
                    break;
                }
            }
            
            $this->_eventDispatcher->dispatch('rollback');
        }
        
        $this->_eventDispatcher->dispatch('stop');
    }
    
    public function migrate($revision, $environment)
    {
        $this->executeMigration($revision, $environment, 1);
        return $this;
    }
    
    public function rollback($revision, $environment)
    {
        $this->executeMigration($revision, $environment, -1);
        return $this;
    }

    public function onStart($listener)
    {
        $this->_eventDispatcher->addListener('start', $listener);
        return $this;
    }
    
    public function onBeforeMigrate($listener)
    {
        $this->_eventDispatcher->addListener('before_migrate', $listener);
        return $this;
    }
    
    public function onBeforeMigrateRevision($listener)
    {
        $this->_eventDispatcher->addListener('before_migrate_revision', $listener);
        return $this;
    }
    
    public function onMigrateRevision($listener)
    {
        $this->_eventDispatcher->addListener('migrate_revision', $listener);
        return $this;
    }
    
    public function onMigrate($listener)
    {
        $this->_eventDispatcher->addListener('migrate', $listener);
        return $this;
    }
    
    public function onBeforeRollback($listener)
    {
        $this->_eventDispatcher->addListener('before_rollback', $listener);
        return $this;
    }
    
    public function onBeforeRollbackRevision($listener)
    {
        $this->_eventDispatcher->addListener('before_rollback_revision', $listener);
        return $this;
    }
    
    public function onRollbackRevision($listener)
    {
        $this->_eventDispatcher->addListener('rollback_revision', $listener);
        return $this;
    }
    
    public function onRollback($listener)
    {
        $this->_eventDispatcher->addListener('rollback', $listener);
        return $this;
    }
    
    public function onStop($listener)
    {
        $this->_eventDispatcher->addListener('stop', $listener);
        return $this;
    }
}