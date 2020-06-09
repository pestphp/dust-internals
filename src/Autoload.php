<?php

namespace Dust;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;
use Pest\Plugin;
use Pest\Support\Backtrace;
use Pest\Support\HigherOrderMessageCollection;
use Pest\TestSuite;
use SebastianBergmann\Exporter\Exporter;

Plugin::uses(TestCase::class);

/**
 * Sets the base url of the test suite.
 */
function baseUrl(string $baseUrl = null): string
{
    static $currentBaseUrl = 'http://localhost:8080';

    if ($baseUrl !== null) {
        $currentBaseUrl = $baseUrl;
    }

    return $currentBaseUrl;
}

/**
 * @param Page|string $url
 *
 * @return Browser|mixed
 */
function browse($url)
{
    if (TestSuite::getInstance()->test !== null) {
        return test()->browse($url);
    }

    return new class($url) {
        /**
         * Holds the higher order
         * messages that are chainable.
         *
         * @var HigherOrderMessageCollection
         */
        private $chains;

        /**
         * The test description.
         *
         * @var string|null
         */
        private $description;

        /**
         * Creates a new instance.
         *
         * @param Page|string $url
         */
        public function __construct($url)
        {
            $this->chains = new HigherOrderMessageCollection();

            /** @var Browser $this */
            $this->visit($url);
        }

        /**
         * @param array<int, mixed> $arguments [description]
         */
        public function __call(string $name, array $arguments): self
        {
            $this->chains
                ->add(Backtrace::file(), Backtrace::line(), $name, $arguments);

            $exporter = new Exporter();

            if ($this->description !== null) {
                $this->description .= ' → ';
            }

            $this->description .= sprintf('%s %s', $name, $exporter->shortenedRecursiveExport($arguments));

            return $this;
        }

        public function __destruct()
        {
            $chains = $this->chains;

            test($this->description, function () use ($chains): void {
                /** @var TestCase $this */
                $this->browse(function (Browser $browser) use ($chains): void {
                    $chains->chain($browser);
                });
            });
        }
    };
}
