<?php

declare(strict_types=1);

namespace Tests\CoRex\IoC\Helpers;

class TestDependencyInjection
{
    /** @var TestInjectedInterface */
    private $testInjected;

    /**
     * TestDependencyInjection.
     *
     * @param TestInjectedInterface $testInjected
     * @param string $test
     */
    public function __construct(TestInjectedInterface $testInjected, string $test)
    {
        $this->testInjected = $testInjected;
    }
}