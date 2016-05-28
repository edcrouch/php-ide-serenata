<?php

namespace PhpIntegrator;

use Exception;

use Doctrine\Common\Cache\FilesystemCache;

/**
 * Main application class.
 */
class Application
{
    /**
     * @var FilesystemCache
     */
    protected $filesystemCache;

    /**
     * Handles the application process.
     *
     * @param array $arguments The arguments to pass.
     *
     * @return mixed
     */
    public function handle(array $arguments)
    {
        $programName = array_shift($arguments);
        $command = array_shift($arguments);
        array_unshift($arguments, $programName);

        $commands = [
            '--class-list'          => 'ClassList',
            '--class-info'          => 'ClassInfo',
            '--functions'           => 'GlobalFunctions',
            '--constants'           => 'GlobalConstants',
            '--reindex'             => 'Reindex',
            '--resolve-type'        => 'ResolveType',
            '--localize-type'       => 'LocalizeType',
            '--semantic-lint'       => 'SemanticLint',
            '--available-variables' => 'AvailableVariables',
            '--variable-types'      => 'VariableTypes',
            '--deduce-types'        => 'DeduceTypes'
        ];

        if (isset($commands[$command])) {
            $className = "\\PhpIntegrator\\Application\\Command\\{$commands[$command]}";

            /** @var \PhpIntegrator\Application\CommandInterface $command */
            $command = new $className($this->getFilesystemCache());

            if (interface_exists('Throwable')) {
                // PHP >= 7.
                try {
                    return $command->execute($arguments);
                } catch (\Throwable $e) {
                    return $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage();
                }
            } else {
                // PHP < 7
                try {
                    return $command->execute($arguments);
                } catch (Exception $e) {
                    return $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage();
                }
            }
        }

        $supportedCommands = implode(', ', array_keys($commands));

        echo "Unknown command {$command}, supported commands: {$supportedCommands}";
    }

    /**
     * Retrieves an instance of FilesystemCache. The object will only be created once if needed.
     *
     * @return FilesystemCache
     */
    protected function getFilesystemCache()
    {
        if (!$this->filesystemCache instanceof FilesystemCache) {
            $this->filesystemCache = new FilesystemCache(
                sys_get_temp_dir() . '/php-integrator-base/'
            );
        }

        return $this->filesystemCache;
    }
}
