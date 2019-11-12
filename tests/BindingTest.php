<?php

declare(strict_types=1);

namespace Tests\CoRex\IoC;

use CoRex\Helpers\Obj;
use CoRex\IoC\Binding;
use CoRex\IoC\Exceptions\IoCException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\IoC\Helpers\BaseTest;
use Tests\CoRex\IoC\Helpers\BaseTestInterface;
use Tests\CoRex\IoC\Helpers\Test;

class BindingTest extends TestCase
{
    /**
     * Test constructor class.
     *
     * @throws Exception
     */
    public function testConstructorClass(): void
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) === 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);

        $this->assertEquals($classOrInterface, Obj::getProperty('classOrInterface', $binding));
        $this->assertFalse(Obj::getProperty('isInterface', $binding));
        $this->assertEquals($instanceClass, Obj::getProperty('instanceClass', $binding));
        $this->assertEquals($shared, Obj::getProperty('shared', $binding));
    }

    /**
     * Test constructor interface.
     *
     * @throws Exception
     */
    public function testConstructorInterface(): void
    {
        $classOrInterface = BaseTestInterface::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) === 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);

        $this->assertEquals($classOrInterface, Obj::getProperty('classOrInterface', $binding));
        $this->assertTrue(Obj::getProperty('isInterface', $binding));
        $this->assertEquals($instanceClass, Obj::getProperty('instanceClass', $binding));
        $this->assertEquals($shared, Obj::getProperty('shared', $binding));
    }

    /**
     * Test constructor not class.
     *
     * @throws Exception
     */
    public function testConstructorNotClass(): void
    {
        $classOrInterface = md5((string)mt_rand(1, 100000));
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) === 1;

        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class ' . $classOrInterface . ' does not exist.');

        new Binding($classOrInterface, $instanceClass, $shared);
    }

    /**
     * Test constructor not instance class.
     *
     * @throws Exception
     */
    public function testConstructorNotInstanceClass(): void
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = md5((string)mt_rand(1, 100000));
        $shared = mt_rand(0, 1) === 1;

        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class ' . $instanceClass . ' does not exist.');

        new Binding($classOrInterface, $instanceClass, $shared);
    }

    /**
     * Test is shared.
     *
     * @throws Exception
     */
    public function testIsShared(): void
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) === 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);
        $this->assertEquals($shared, call_user_func([$binding, 'isShared']));
    }

    /**
     * Test get instance class.
     *
     * @throws Exception
     */
    public function testGetInstanceClass(): void
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) === 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);
        $this->assertEquals($instanceClass, call_user_func([$binding, 'getInstanceClass']));
    }
}
