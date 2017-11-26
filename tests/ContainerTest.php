<?php

namespace Tests\CoRex\IoC;

use CoRex\IoC\Binding;
use CoRex\IoC\Container;
use CoRex\IoC\Exception;
use CoRex\Support\Obj;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\IoC\Helpers\BaseTest;
use Tests\CoRex\IoC\Helpers\BaseTestInterface;
use Tests\CoRex\IoC\Helpers\Test;
use Tests\CoRex\IoC\Helpers\TestDependencyInjection;
use Tests\CoRex\IoC\Helpers\TestInjected;
use Tests\CoRex\IoC\Helpers\TestInjectedInterface;
use Tests\CoRex\IoC\Helpers\TestNoExtends;

class ContainerTest extends TestCase
{
    /**
     * Test constructor.
     */
    public function testConstructor()
    {
        $container = $this->container();
        $this->assertEquals([], Obj::getProperty($container, 'bindings'));
        $this->assertEquals([], Obj::getProperty($container, 'instances'));
    }

    /**
     * Test get instance.
     */
    public function testGetInstance()
    {
        $container = $this->container();
        $this->assertNotNull($container);
        $this->assertEquals(Container::class, get_class($container));
    }

    /**
     * Test clear.
     */
    public function testClear()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $container->clear();
        $this->assertEquals([], $container->getBindings());
    }

    /**
     * Test get bindings none.
     */
    public function testGetBindingsNone()
    {
        $this->assertEquals([], $this->container()->getBindings());
    }

    /**
     * Test get bindings one.
     */
    public function testGetBindingsOne()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $binding = new Binding(Test::class, Test::class, false);
        $this->assertEquals([Test::class => $binding], $container->getBindings());
    }

    /**
     * Test get binding.
     */
    public function testGetBinding()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $binding = new Binding(Test::class, Test::class, false);
        $this->assertEquals($binding, $container->getBinding(Test::class));
    }

    /**
     * Test has.
     */
    public function testHas()
    {
        $container = $this->container();
        $this->assertFalse($container->has(Test::class));
        $container->bind(Test::class);
        $this->assertTrue($container->has(Test::class));
    }

    /**
     * Test has instance.
     */
    public function testHasInstance()
    {
        $container = $this->container();
        $this->assertFalse($container->hasInstance(Test::class));
        $container->instance(Test::class, new Test());
        $this->assertTrue($container->hasInstance(Test::class));
    }

    /**
     * Test is shared.
     */
    public function testIsShared()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $this->assertFalse($container->isShared(Test::class));

        $container->clear();
        $container->singleton(Test::class);
        $this->assertTrue($container->isShared(Test::class));
    }

    /**
     * Test is singleton.
     */
    public function testIsSingleton()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $this->assertFalse($container->isSingleton(Test::class));

        $container->clear();
        $container->singleton(Test::class);
        $this->assertTrue($container->isSingleton(Test::class));
    }

    /**
     * Test forget.
     */
    public function testForget()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $container->bind(TestNoExtends::class);

        $this->assertTrue($container->has(Test::class));
        $this->assertTrue($container->has(TestNoExtends::class));

        $container->forget(Test::class);

        $this->assertFalse($container->has(Test::class));
        $this->assertTrue($container->has(TestNoExtends::class));
    }

    /**
     * Test bind.
     */
    public function testBind()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $this->assertTrue($container->has(Test::class));
        $this->assertFalse($container->isShared(Test::class));
    }

    /**
     * Test singleton.
     */
    public function testSingleton()
    {
        $container = $this->container();
        $container->singleton(Test::class);
        $this->assertTrue($container->has(Test::class));
        $this->assertTrue($container->isShared(Test::class));
    }

    /**
     * Test instance.
     */
    public function testInstance()
    {
        $this->testHasInstance();
    }

    /**
     * Test make.
     */
    public function testMake()
    {
        $container = $this->container();
        $container->bind(BaseTestInterface::class, Test::class);
        $instance = $container->make(BaseTestInterface::class);
        $this->assertEquals(Test::class, get_class($instance));
    }

    /**
     * Test make no extends.
     */
    public function testMakeNoExtends()
    {
        $message = 'Class {class} does not extend {interface}';
        $message = str_replace('{class}', TestNoExtends::class, $message);
        $message = str_replace('{interface}', BaseTest::class, $message);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);
        $container = $this->container();
        $container->bind(BaseTest::class, TestNoExtends::class);
        $container->make(BaseTest::class);
    }

    /**
     * Test make no implements.
     */
    public function testMakeNoImplements()
    {
        $message = 'Class {class} does not implement {interface}';
        $message = str_replace('{class}', TestNoExtends::class, $message);
        $message = str_replace('{interface}', BaseTestInterface::class, $message);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);
        $container = $this->container();
        $container->bind(BaseTestInterface::class, TestNoExtends::class);
        $container->make(BaseTestInterface::class);
    }

    /**
     * Test dependency injection not found.
     */
    public function testDependencyInjectionNotFound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class ' . TestInjectedInterface::class . ' does not exist.');
        $container = $this->container();
        $container->bind(TestDependencyInjection::class);
        $container->make(TestDependencyInjection::class);
    }

    /**
     * Test dependency injection injected.
     */
    public function testDependencyInjectionInjected()
    {
        $check = md5(mt_rand(1, 100000));
        $container = $this->container();
        $container->bind(TestInjectedInterface::class, TestInjected::class);
        $container->bind(TestDependencyInjection::class);
        $instance = $container->make(TestDependencyInjection::class, ['test' => $check]);
        $this->assertEquals(TestInjected::class, get_class(Obj::getProperty($instance, 'testInjected')));
    }

    /**
     * Container.
     *
     * @return Container
     */
    private function container()
    {
        $container = Container::getInstance();
        $container->clear();
        return $container;
    }
}
