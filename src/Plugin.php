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

        $this->init();

        exit(0);
    }

    private function init(): void
    {
        $app = (new TestCase())->createApplication();

        $app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $app->register(\Laravel\Dusk\DuskServiceProvider::class);

        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

        $status = $kernel->call('dusk:chrome-driver', [], $this->output);

        $kernel->terminate(new \Symfony\Component\Console\Input\ArrayInput([]), $status);

        exit($status);
    }
}
