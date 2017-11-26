<?php

namespace Tests\CoRex\IoC\Helpers;

class TestDependencyInjection
{
    private $testInjected;

    /**
     * TestDependencyInjection constructor.
     *
     * @param TestInjectedInterface $testInjected
     * @param string $test
     */
    public function __construct(TestInjectedInterface $testInjected, $test)
    {
        $this->testInjected = $testInjected;
    }
}