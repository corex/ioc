<?php

namespace Tests\CoRex\IoC;

use CoRex\Helpers\Obj;
use CoRex\IoC\Binding;
use CoRex\IoC\Exception;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\IoC\Helpers\BaseTest;
use Tests\CoRex\IoC\Helpers\BaseTestInterface;
use Tests\CoRex\IoC\Helpers\Test;

class BindingTest extends TestCase
{
    /**
     * Test constructor class.
     * @throws \Exception
     */
    public function testConstructorClass()
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) == 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);

        $this->assertEquals($classOrInterface, Obj::getProperty('classOrInterface', $binding));
        $this->assertFalse(Obj::getProperty('isInterface', $binding));
        $this->assertEquals($instanceClass, Obj::getProperty('instanceClass', $binding));
        $this->assertEquals($shared, Obj::getProperty('shared', $binding));
    }

    /**
     * Test constructor interface.
     * @throws \Exception
     */
    public function testConstructorInterface()
    {
        $classOrInterface = BaseTestInterface::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) == 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);

        $this->assertEquals($classOrInterface, Obj::getProperty('classOrInterface', $binding));
        $this->assertTrue(Obj::getProperty('isInterface', $binding));
        $this->assertEquals($instanceClass, Obj::getProperty('instanceClass', $binding));
        $this->assertEquals($shared, Obj::getProperty('shared', $binding));
    }

    /**
     * Test constructor not class.
     * @throws \Exception
     */
    public function testConstructorNotClass()
    {
        $classOrInterface = md5(mt_rand(1, 100000));
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) == 1;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class ' . $classOrInterface . ' does not exist.');

        new Binding($classOrInterface, $instanceClass, $shared);
    }

    /**
     * Test constructor not instance class.
     * @throws \Exception
     */
    public function testConstructorNotInstanceClass()
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = md5(mt_rand(1, 100000));
        $shared = mt_rand(0, 1) == 1;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class ' . $instanceClass . ' does not exist.');

        new Binding($classOrInterface, $instanceClass, $shared);
    }

    /**
     * Test is shared.
     * @throws \Exception
     */
    public function testIsShared()
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) == 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);
        $this->assertEquals($shared, call_user_func([$binding, 'isShared']));
    }

    /**
     * Test get instance class.
     * @throws \Exception
     */
    public function testGetInstanceClass()
    {
        $classOrInterface = BaseTest::class;
        $instanceClass = Test::class;
        $shared = mt_rand(0, 1) == 1;
        $binding = new Binding($classOrInterface, $instanceClass, $shared);
        $this->assertEquals($instanceClass, call_user_func([$binding, 'getInstanceClass']));
    }
}
