<?php

namespace Tests\CoRex\IoC;

use CoRex\IoC\Resolver;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\IoC\Helpers\TestDependencyInjection;
use Tests\CoRex\IoC\Helpers\TestInjectedInterface;

class ResolverTest extends TestCase
{
    /**
     * Test resolve constructor.
     *
     * @throws \CoRex\IoC\Exception
     */
    public function testResolveConstructor()
    {
        $check = md5(mt_rand(1, 100000));
        $resolvedParameters = Resolver::resolveConstructor(TestDependencyInjection::class, [
            'test' => $check
        ], false);
        $this->assertEquals([TestInjectedInterface::class, $check], $resolvedParameters);
    }
}
