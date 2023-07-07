<?php

namespace MalteKuhr\LaravelGPT\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class GPTMakeCommand extends Command
{
    abstract protected function getDefaultNamespace(string $name): string;

    abstract protected function getClassName(): string;

    protected function configure()
    {
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'Remove all doc block comments');
    }

    protected function getStub(string $className): string
    {
        $stubPath = __DIR__ . "/../Stubs/{$className}.stub";
        $stub = file_get_contents($stubPath);

        return $stub;
    }

    protected function getNamespaceAndClassName(string $name): array
    {
        $segments = explode('\\', $name);
        $className = array_pop($segments);
        $namespace = implode('\\', $segments);

        // Add the 'getClassName()' suffix if not already present
        if (!preg_match('/'.$this->getClassName().'$/', $className)) {
            $className .= $this->getClassName();
        }

        if (empty($namespace)) {
            $namespace = $this->getDefaultNamespace(
                substr($className, 0, -strlen($this->getClassName()))
            );
        }

        return [$namespace, $className];
    }

    protected function createDirectoryIfNeeded(string $directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    protected function verifyFileDoesNotExist(string $path)
    {
        if (file_exists($path)) {
            $this->newLine();
            $this->line("  <bg=red;fg=white> ERROR </><fg=black> {$this->getClassName()} already exists.</>");
            $this->newLine();
            return false;
        }

        return true;
    }

    public function handle()
    {
        [$namespace, $className] = $this->getNamespaceAndClassName($this->argument('name'));

        $stub = $this->getStub($this->getClassName());

        // If the 'clean' option is set, remove doc block comments
        if ($this->option('clean')) {
            $stub = $this->removeDocBlocks($stub);
        }

        $stub = str_replace('{NAMESPACE}', $namespace, $stub);
        $stub = str_replace('{NAME}', $className, $stub);

        $fileName = "{$className}.php";
        $directoryPath = str_replace('\\', '/', $namespace);
        $path = base_path("app/{$directoryPath}/{$fileName}");
        $directory = dirname($path);

        $this->createDirectoryIfNeeded($directory);

        if (!$this->verifyFileDoesNotExist($path)) {
            return;
        }

        file_put_contents($path, $stub);

        $this->newLine();
        $this->output->writeln("  <bg=blue;fg=white> INFO </><fg=black> {$this->getClassName()} <options=bold>[App\\$namespace\\$className.php]</> created successfully.</>");
        $this->newLine();
    }

    protected function removeDocBlocks(string $code): string
    {
        $lines = explode("\n", $code);
        $cleanLines = [];
        $isInDocBlock = false;

        foreach ($lines as $line) {
            if (strpos($line, '/**') !== false) {
                $isInDocBlock = true;
            }
            if (!$isInDocBlock) {
                $cleanLines[] = $line;
            }
            if (strpos($line, '*/') !== false) {
                $isInDocBlock = false;
            }
        }

        return implode("\n", $cleanLines);
    }
}
