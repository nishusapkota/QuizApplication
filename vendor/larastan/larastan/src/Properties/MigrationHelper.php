<?php

declare(strict_types=1);

namespace Larastan\Larastan\Properties;

use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\Parser\ParserErrorsException;
use PHPStan\Reflection\ReflectionProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

use function count;
use function database_path;
use function is_dir;
use function iterator_to_array;

class MigrationHelper
{
    /** @var Parser */
    private $parser;

    /** @var string[] */
    private $databaseMigrationPath;

    /** @var bool */
    private $disableMigrationScan;

    /** @var FileHelper */
    private $fileHelper;

    /** @var ReflectionProvider */
    private $reflectionProvider;

    /**
     * @param  string[]  $databaseMigrationPath
     */
    public function __construct(
        Parser $parser,
        array $databaseMigrationPath,
        FileHelper $fileHelper,
        bool $disableMigrationScan,
        ReflectionProvider $reflectionProvider
    ) {
        $this->parser = $parser;
        $this->databaseMigrationPath = $databaseMigrationPath;
        $this->fileHelper = $fileHelper;
        $this->disableMigrationScan = $disableMigrationScan;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @param  array<string, SchemaTable>  $tables
     * @return array<string, SchemaTable>
     */
    public function initializeTables(array $tables = []): array
    {
        if ($this->disableMigrationScan) {
            return $tables;
        }

        if (count($this->databaseMigrationPath) === 0) {
            $this->databaseMigrationPath = [database_path('migrations')];
        }

        $schemaAggregator = new SchemaAggregator($this->reflectionProvider, $tables);
        $filesArray = $this->getMigrationFiles();

        if (empty($filesArray)) {
            return $tables;
        }

        uasort($filesArray, function (SplFileInfo $a, SplFileInfo $b) {
            return $a->getFilename() <=> $b->getFilename();
        });

        foreach ($filesArray as $file) {
            try {
                $schemaAggregator->addStatements($this->parser->parseFile($file->getPathname()));
            } catch (ParserErrorsException $e) {
                continue;
            }
        }

        return $schemaAggregator->tables;
    }

    /**
     * @return SplFileInfo[]
     */
    private function getMigrationFiles(): array
    {
        /** @var SplFileInfo[] $migrationFiles */
        $migrationFiles = [];

        foreach ($this->databaseMigrationPath as $additionalPath) {
            $absolutePath = $this->fileHelper->absolutizePath($additionalPath);

            if (is_dir($absolutePath)) {
                $migrationFiles += iterator_to_array(
                    new RegexIterator(
                        new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath)),
                        '/\.php$/i'
                    )
                );
            }
        }

        return $migrationFiles;
    }
}
