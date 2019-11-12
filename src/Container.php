<?php

declare(strict_types=1);

namespace CoRex\IoC;

use CoRex\IoC\Exceptions\IoCException;

class Container
{
    /** @var Container */
    private static $instance;

    /** @var Binding[] */
    private $bindings;

    /** @var object[] */
    private $instances;

    /**
     * Container.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Get instance.
     *
     * @return Container
     */
    public static function getInstance(): self
    {
        if (!is_object(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Clear.
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }

    /**
     * Get bindings.
     *
     * @return Binding[]
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get binding.
     *
     * @param string $classOrInterface
     * @return Binding
     */
    public function getBinding(string $classOrInterface): ?Binding
    {
        if ($this->has($classOrInterface)) {
            return $this->bindings[$classOrInterface];
        }
        return null;
    }

    /**
     * Has.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function has(string $classOrInterface): bool
    {
        return array_key_exists($classOrInterface, $this->bindings);
    }

    /**
     * Has instance.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function hasInstance(string $classOrInterface): bool
    {
        return array_key_exists($classOrInterface, $this->instances) && is_object($this->instances[$classOrInterface]);
    }

    /**
     * Is shared.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function isShared(string $classOrInterface): bool
    {
        if ($this->has($classOrInterface)) {
            return $this->getBinding($classOrInterface)->isShared();
        }
        return false;
    }

    /**
     * Is singleton.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function isSingleton(string $classOrInterface): bool
    {
        return $this->isShared($classOrInterface);
    }

    /**
     * Forget.
     *
     * @param string $classOrInterface
     */
    public function forget(string $classOrInterface): void
    {
        if ($this->has($classOrInterface)) {
            unset($this->bindings[$classOrInterface]);
        }
        if ($this->hasInstance($classOrInterface)) {
            unset($this->instances[$classOrInterface]);
        }
    }

    /**
     * Bind.
     *
     * @param string $classOrInterface
     * @param string $instanceClass Default null.
     * @param bool $shared Default false.
     * @throws IoCException
     */
    public function bind(string $classOrInterface, ?string $instanceClass = null, bool $shared = false): void
    {
        // Check if already bound.
        if ($this->has($classOrInterface)) {
            throw new IoCException('Class or interface ' . $classOrInterface . ' already bound.');
        }

        $this->validateClassOrInterface($classOrInterface);
        if ($instanceClass === null) {
            $instanceClass = $classOrInterface;
        }
        $this->validateInstanceClass($classOrInterface, $instanceClass);
        $this->bindings[$classOrInterface] = new Binding($classOrInterface, $instanceClass, $shared);
    }

    /**
     * Singleton.
     *
     * @param string $classOrInterface
     * @param string $instanceClass Default null.
     * @throws IoCException
     */
    public function singleton(string $classOrInterface, ?string $instanceClass = null): void
    {
        $this->bind($classOrInterface, $instanceClass, true);
    }

    /**
     * Instance.
     *
     * @param string $classOrInterface
     * @param object $object
     * @throws IoCException
     */
    public function instance(string $classOrInterface, object $object): void
    {
        $this->validateObject($classOrInterface, $object);
        if (!$this->has($classOrInterface)) {
            $this->singleton($classOrInterface, get_class($object));
        }
        $this->getBinding($classOrInterface)->setShared();
        $this->instances[$classOrInterface] = $object;
    }

    /**
     * Make.
     *
     * @param string $classOrInterface
     * @param mixed[] $resolveParameters Default [].
     * @return object
     * @throws IoCException
     */
    public function make(string $classOrInterface, array $resolveParameters = []): object
    {
        // Get binding details.
        $binding = $this->getBinding($classOrInterface);
        $isShared = false;
        $instanceClass = null;
        if ($binding !== null) {
            $isShared = $binding->isShared();
            $instanceClass = $binding->getInstanceClass();
        }

        // Validate classes.
        if ($instanceClass === null) {
            $instanceClass = $classOrInterface;
        }
        $this->validateClassOrInterface($classOrInterface);
        $this->validateInstanceClass($classOrInterface, $instanceClass);

        // If shared and has instance, return it.
        if ($isShared && $this->hasInstance($classOrInterface)) {
            return $this->instances[$classOrInterface];
        }

        // Resolve and create instance.
        $resolvedParameters = Resolver::resolveConstructor($instanceClass, $resolveParameters);
        $instance = $this->newInstance($instanceClass, $resolvedParameters);

        // If shared, store instance.
        if ($isShared) {
            $this->instances[$classOrInterface] = $instance;
        }

        return $instance;
    }

    /**
     * Validate class or interface.
     *
     * @param string $classOrInterface
     * @throws IoCException
     */
    private function validateClassOrInterface(string $classOrInterface): void
    {
        if (!$this->classExists($classOrInterface)) {
            throw new IoCException('Class or interface ' . $classOrInterface . ' does not exist.');
        }
    }

    /**
     * Validate instance class.
     *
     * @param string $classOrInterface
     * @param string $instanceClass
     * @throws IoCException
     */
    private function validateInstanceClass(string $classOrInterface, string $instanceClass): void
    {
        // CHeck if concrete class exists.
        if (!class_exists($instanceClass)) {
            throw new IoCException('Class ' . $instanceClass . ' does not exist.');
        }

        // If interface, check if instance class implements $classOrInterface.
        if (interface_exists($classOrInterface)) {
            if (!in_array($classOrInterface, class_implements($instanceClass))) {
                throw new IoCException('Class ' . $instanceClass . ' does not implement ' . $classOrInterface);
            }
        }

        // If class, check if instance class extends $classOrInterface.
        if (class_exists($classOrInterface)) {
            if ($classOrInterface !== $instanceClass && !in_array($classOrInterface, class_parents($instanceClass))) {
                throw new IoCException('Class ' . $instanceClass . ' does not extend ' . $classOrInterface);
            }
        }
    }

    /**
     * Validate object.
     *
     * @param string $classOrInterface
     * @param object $object
     * @throws IoCException
     */
    private function validateObject(string $classOrInterface, object $object): void
    {
        $this->validateInstanceClass($classOrInterface, get_class($object));
    }

    /**
     * New instance.
     *
     * @param string $class
     * @param mixed[] $params
     * @return object
     * @throws IoCException
     */
    private function newInstance(string $class, array $params): object
    {
        try {
            $reflectionClass = new \ReflectionClass($class);
            return $reflectionClass->newInstanceArgs($params);
        } catch (\ReflectionException $e) {
            throw new IoCException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Class exist.
     *
     * @param string $class
     * @return bool
     */
    private function classExists(string $class): bool
    {
        $classOrInterfaceExists = false;
        if (interface_exists($class)) {
            $classOrInterfaceExists = true;
        } elseif (class_exists($class)) {
            $classOrInterfaceExists = true;
        }
        return $classOrInterfaceExists;
    }
}