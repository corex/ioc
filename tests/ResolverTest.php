<?php

namespace Tests\CoRex\IoC;

use CoRex\IoC\Exception;
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

    /**
     * Test resolve constructor null.
     *
     * @throws Exception
     */
    public function testResolveConstructorNull()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class test does not exist');
        Resolver::resolveConstructor('test', [], false);
    }

    /**
     * Test resolve constructor parameter not found.
     *
     * @throws Exception
     */
    public function testResolveConstructorParameterNotFound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Parameter test not found.');
        Resolver::resolveConstructor(TestDependencyInjection::class, [], false);
    }
}
