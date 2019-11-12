<?php

declare(strict_types=1);

namespace Tests\CoRex\IoC;

use CoRex\Helpers\Obj;
use CoRex\IoC\Binding;
use CoRex\IoC\Container;
use CoRex\IoC\Exceptions\IoCException;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Tests\CoRex\IoC\Helpers\BadClass;
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
     * Test.
     *
     * @throws ReflectionException
     */
    public function testConstructor(): void
    {
        $container = $this->container();
        $this->assertEquals([], Obj::getProperty('bindings', $container));
        $this->assertEquals([], Obj::getProperty('instances', $container));
    }

    /**
     * Test get instance.
     */
    public function testGetInstance(): void
    {
        $container = $this->container();
        $this->assertNotNull($container);
        $this->assertEquals(Container::class, get_class($container));
    }

    /**
     * Test clear.
     *
     * @throws Exception
     */
    public function testClear(): void
    {
        $container = $this->container();
        $container->bind(Test::class);
        $container->clear();
        $this->assertEquals([], $container->getBindings());
    }

    /**
     * Test get bindings none.
     */
    public function testGetBindingsNone(): void
    {
        $this->assertEquals([], $this->container()->getBindings());
    }

    /**
     * Test get bindings one.
     *
     * @throws Exception
     */
    public function testGetBindingsOne(): void
    {
        $container = $this->container();
        $container->bind(Test::class);
        $binding = new Binding(Test::class, Test::class, false);
        $this->assertEquals([Test::class => $binding], $container->getBindings());
    }

    /**
     * Test get binding.
     *
     * @throws Exception
     */
    public function testGetBinding(): void
    {
        $container = $this->container();
        $container->bind(Test::class);
        $binding = new Binding(Test::class, Test::class, false);
        $this->assertEquals($binding, $container->getBinding(Test::class));
    }

    /**
     * Test has.
     *
     * @throws Exception
     */
    public function testHas(): void
    {
        $container = $this->container();
        $this->assertFalse($container->has(Test::class));
        $container->bind(Test::class);
        $this->assertTrue($container->has(Test::class));
    }

    /**
     * Test has instance.
     *
     * @throws Exception
     */
    public function testHasInstance(): void
    {
        $container = $this->container();
        $this->assertFalse($container->hasInstance(Test::class));
        $container->instance(Test::class, new Test());
        $this->assertTrue($container->hasInstance(Test::class));
        $this->assertTrue($container->getBinding(Test::class)->isShared());
    }

    /**
     * Test is shared.
     *
     * @throws Exception
     */
    public function testIsShared(): void
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
     * @throws Exception
     */
    public function testIsNotShared(): void
    {
        $container = $this->container();
        $this->assertFalse($container->isShared(Test::class));
    }

    /**
     * Test is singleton.
     *
     * @throws Exception
     */
    public function testIsSingleton(): void
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
     * @throws Exception
     */
    public function testForget(): void
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
     * @throws Exception
     */
    public function testBind(): void
    {
        $container = $this->container();
        $container->bind(Test::class);
        $this->assertTrue($container->has(Test::class));
        $this->assertFalse($container->isShared(Test::class));
    }

    /**
     * Test bind already bound.
     *
     * @throws Exception
     */
    public function testBindAlreadyBound(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class or interface ' . Test::class . ' already bound.');
        $container = $this->container();
        $container->bind(Test::class);
        $container->bind(Test::class);
    }

    /**
     * Test singleton.
     *
     * @throws Exception
     */
    public function testSingleton(): void
    {
        $container = $this->container();
        $container->singleton(Test::class);
        $this->assertTrue($container->has(Test::class));
        $this->assertTrue($container->isShared(Test::class));
    }

    /**
     * Test instance.
     *
     * @throws Exception
     */
    public function testInstance(): void
    {
        $container = $this->container();
        $container->bind(Test::class);
        $this->assertFalse($container->hasInstance(Test::class));
        $container->instance(Test::class, new Test());
        $this->assertTrue($container->hasInstance(Test::class));
        $this->assertTrue($container->getBinding(Test::class)->isShared());
    }

    /**
     * Test make.
     *
     * @throws IoCException
     */
    public function testMake(): void
    {
        $container = $this->container();
        $container->bind(BaseTestInterface::class, Test::class);
        $instance = $container->make(BaseTestInterface::class);
        $this->assertEquals(Test::class, get_class($instance));
    }

    /**
     * Test make with singleton.
     *
     * @throws Exception
     */
    public function testMakeWithSingleton(): void
    {
        $container = $this->container();
        $container->singleton(BaseTestInterface::class, Test::class);

        // Make instance 1 and set test value.
        $instance1 = $container->make(BaseTestInterface::class);
        $instance1->test = md5((string)mt_rand(1, 100000));

        // Make instance 2 and test previous set value.
        $instance2 = $container->make(BaseTestInterface::class);

        $this->assertEquals($instance1, $instance2);
    }

    /**
     * Test make no extends.
     *
     * @throws Exception
     */
    public function testMakeNoExtends(): void
    {
        $message = 'Class {class} does not extend {interface}';
        $message = str_replace('{class}', TestNoExtends::class, $message);
        $message = str_replace('{interface}', BaseTest::class, $message);
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage($message);
        $container = $this->container();
        $container->bind(BaseTest::class, TestNoExtends::class);
        $container->make(BaseTest::class);
    }

    /**
     * Test make no implements.
     *
     * @throws Exception
     */
    public function testMakeNoImplements(): void
    {
        $message = 'Class {class} does not implement {interface}';
        $message = str_replace('{class}', TestNoExtends::class, $message);
        $message = str_replace('{interface}', BaseTestInterface::class, $message);
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage($message);
        $container = $this->container();
        $container->bind(BaseTestInterface::class, TestNoExtends::class);
        $container->make(BaseTestInterface::class);
    }

    /**
     * Test dependency injection not found.
     *
     * @throws Exception
     */
    public function testDependencyInjectionNotFound(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class ' . TestInjectedInterface::class . ' does not exist.');
        $container = $this->container();
        $container->bind(TestDependencyInjection::class);
        $container->make(TestDependencyInjection::class);
    }

    /**
     * Test dependency injection injected.
     *
     * @throws Exception
     */
    public function testDependencyInjectionInjected(): void
    {
        $check = md5((string)mt_rand(1, 100000));
        $container = $this->container();
        $container->bind(TestInjectedInterface::class, TestInjected::class);
        $container->bind(TestDependencyInjection::class);
        $instance = $container->make(TestDependencyInjection::class, ['test' => $check]);
        $this->assertEquals(TestInjected::class, get_class(Obj::getProperty('testInjected', $instance)));
    }

    /**
     * Test validateClassOrInterface().
     *
     * @throws ReflectionException
     */
    public function testValidateClassOrInterface(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class or interface unknown.class does not exist.');
        $container = $this->container();
        Obj::callMethod('validateClassOrInterface', $container, [
            'classOrInterface' => 'unknown.class'
        ]);
    }

    /**
     * Test new instance class not found.
     *
     * @throws ReflectionException
     */
    public function testNewInstanceClassNotFound(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('Class unknown.class does not exist');
        $container = $this->container();
        Obj::callMethod('newInstance', $container, [
            'class' => 'unknown.class',
            'params' => []
        ]);
    }

    /**
     * Test newInstance reflection exception.
     *
     * @throws ReflectionException
     */
    public function testNewInstanceReflectionException(): void
    {
        $this->expectException(IoCException::class);
        $this->expectExceptionMessage('fail.on.purpose');
        $container = $this->container();
        Obj::callMethod('newInstance', $container, [
            'class' => BadClass::class,
            'params' => []
        ]);
    }

    /**
     * Test new instance missing parameters.
     *
     * @throws ReflectionException
     */
    public function testNewInstanceMissingParameters(): void
    {
        $message = 'Too few arguments to function Tests\CoRex\IoC\Helpers\TestDependencyInjection::__construct(),' .
            ' 0 passed and exactly 2 expected';
        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage($message);
        $container = $this->container();
        Obj::callMethod('newInstance', $container, [
            'class' => TestDependencyInjection::class,
            'params' => []
        ]);
    }

    /**
     * Container.
     *
     * @return Container
     */
    private function container(): Container
    {
        $container = Container::getInstance();
        $container->clear();
        return $container;
    }
}
