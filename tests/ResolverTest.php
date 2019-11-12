<?php

declare(strict_types=1);

namespace Tests\CoRex\IoC;

use CoRex\IoC\Exceptions\IoCException;
use CoRex\IoC\Resolver;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\IoC\Helpers\TestDependencyInjection;
use Tests\CoRex\IoC\Helpers\TestDependencyInjectionDefaultValue;
use Tests\CoRex\IoC\Helpers\TestInjectedInterface;

class ResolverTest extends TestCase
{
    /**
     * Test resolve constructor.
     *
     * @throws IoCException
     */
    public function testResolveConstructor(): void
    {
        $check = md5((string)mt_rand(1, 100000));
        $resolvedParameters = Resolver::resolveConstructor(TestDependencyInjection::class, [
            'test' => $check
        ], false);
        $this->assertEquals([TestInjectedInterface::class, $check], $resolvedParameters);
    }

    /**
     * Test resolveConstructor bad class.
     *
     * @throws IoCException
     */
    public function testResolveConstructorBadClass(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class not.a.class does not exist');
        Resolver::resolveConstructor('not.a.class', [], false);
    }

    /**
     * Test resolve constructor null.
     *
     * @throws IoCException
     */
    public function testResolveConstructorNull(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class test does not exist');
        Resolver::resolveConstructor('test', [], false);
    }

    /**
     * Test resolve constructor parameter not found.
     *
     * @throws IoCException
     */
    public function testResolveConstructorParameterNotFound(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Parameter test not found.');
        Resolver::resolveConstructor(TestDependencyInjection::class, [], false);
    }

    /**
     * Test resolveParameters not found default value.
     *
     * @throws IoCException
     */
    public function testResolveParametersNotFoundDefaultValue(): void
    {
        $check = md5((string)mt_rand(1, 100000));
        $resolvedParameters = Resolver::resolveConstructor(TestDependencyInjectionDefaultValue::class, [
            $check => $check
        ], false);
        $this->assertSame(TestInjectedInterface::class, $resolvedParameters[0]);
        $this->assertSame(TestDependencyInjectionDefaultValue::DEFAULT_VALUE, $resolvedParameters[1]);
    }
}
