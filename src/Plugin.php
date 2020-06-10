<?php

declare(strict_types=1);

namespace Dust;

use Pest\Contracts\Plugins\HandlesArguments;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class Plugin implements HandlesArguments
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Creates a new Plugin instance.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output    = $output;
    }

    public function handleArguments(array $arguments): array
    {
        if (!array_key_exists(1, $arguments) || $arguments[1] !== 'dust-update') {
            return $arguments;
        }

        $this->init($arguments);

        exit(0);
    }

    /**
     * @param array<int, string> $arguments
     */
    private function init(array $arguments): void
    {
        $app = (new TestCase())->createApplication();

        $app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $app->register(\Laravel\Dusk\DuskServiceProvider::class);

        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

        // @todo To be improved..
        unset($arguments[0]);
        unset($arguments[1]);
        $arguments = array_values($arguments);
        if (array_key_exists(0, $arguments)) {
            $version = $arguments[0];
            unset($arguments[0]);
            $arguments['version'] = $version;
        }

        $status = $kernel->call('dusk:chrome-driver', $arguments, $this->output);

        $kernel->terminate(new \Symfony\Component\Console\Input\ArrayInput([]), $status);

        exit($status);
    }
}
