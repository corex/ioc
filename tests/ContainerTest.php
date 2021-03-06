<?php

namespace Tests\CoRex\IoC;

use CoRex\Helpers\Obj;
use CoRex\IoC\Binding;
use CoRex\IoC\Container;
use CoRex\IoC\Exception;
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
        $this->assertEquals([], Obj::getProperty('bindings', $container));
        $this->assertEquals([], Obj::getProperty('instances', $container));
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     * Test is noy shared.
     *
     * @throws \Exception
     */
    public function testIsNotShared()
    {
        $container = $this->container();
        $this->assertFalse($container->isShared(Test::class));
    }

    /**
     * Test is singleton.
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function testForget()
    {
        $container = $this->container();
        $container->singleton(Test::class);
        $container->singleton(TestNoExtends::class);

        $this->assertTrue($container->has(Test::class));
        $this->assertTrue($container->has(TestNoExtends::class));

        $container->make(Test::class);

        $container->forget(Test::class);

        $this->assertFalse($container->has(Test::class));
        $this->assertTrue($container->has(TestNoExtends::class));
    }

    /**
     * Test bind.
     *
     * @throws \Exception
     */
    public function testBind()
    {
        $container = $this->container();
        $container->bind(Test::class);
        $this->assertTrue($container->has(Test::class));
        $this->assertFalse($container->isShared(Test::class));
    }

    /**
     * Test bind already bound.
     *
     * @throws \Exception
     */
    public function testBindAlreadyBound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class or interface ' . Test::class . ' already bound.');
        $container = $this->container();
        $container->bind(Test::class);
        $container->bind(Test::class);
    }

    /**
     * Test singleton.
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function testInstance()
    {
        $this->testHasInstance();
    }

    /**
     * Test make.
     *
     * @throws Exception
     */
    public function testMake()
    {
        $container = $this->container();
        $container->bind(BaseTestInterface::class, Test::class);
        $instance = $container->make(BaseTestInterface::class);
        $this->assertEquals(Test::class, get_class($instance));
    }

    /**
     * Test make with singleton.
     *
     * @throws \Exception
     */
    public function testMakeWithSingleton()
    {
        $container = $this->container();
        $container->singleton(BaseTestInterface::class, Test::class);

        // Make instance 1 and set test value.
        $instance1 = $container->make(BaseTestInterface::class);
        $instance1->test = md5(mt_rand(1, 100000));

        // Make instance 2 and test previous set value.
        $instance2 = $container->make(BaseTestInterface::class);

        $this->assertEquals($instance1, $instance2);
    }

    /**
     * Test make no extends.
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function testDependencyInjectionInjected()
    {
        $check = md5(mt_rand(1, 100000));
        $container = $this->container();
        $container->bind(TestInjectedInterface::class, TestInjected::class);
        $container->bind(TestDependencyInjection::class);
        $instance = $container->make(TestDependencyInjection::class, ['test' => $check]);
        $this->assertEquals(TestInjected::class, get_class(Obj::getProperty('testInjected', $instance)));
    }

    /**
     * Test validateClassOrInterface().
     *
     * @throws \ReflectionException
     */
    public function testValidateClassOrInterface()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class or interface unknown.class does not exist.');
        $container = $this->container();
        Obj::callMethod('validateClassOrInterface', $container, [
            'classOrInterface' => 'unknown.class'
        ]);
    }

    public function testNewInstanceClassNotFound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class unknown.class does not exist');
        $container = $this->container();
        Obj::callMethod('newInstance', $container, [
            'class' => 'unknown.class',
            'params' => []
        ]);
    }

//    public function testNewInstanceMissingParameters()
//    {
////        $this->expectException(Exception::class);
////        $this->expectExceptionMessage('Class unknown.class does not exist.');
//        $container = $this->container();
//
//        $testInjected = new TestInjected();
//
//        Obj::callMethod('newInstance', $container, [
//            'class' => TestDependencyInjection::class,
//            'params' => []
//        ]);
//    }

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
