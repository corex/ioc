<?php

namespace CoRex\IoC;

class Container
{
    private static $instance;
    private $bindings;
    private $instances;

    /**
     * Container constructor.
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
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Clear.
     */
    public function clear()
    {
        $this->bindings = [];
        $this->instances = [];
    }

    /**
     * Get bindings.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get binding.
     *
     * @param string $classOrInterface
     * @return Binding
     */
    public function getBinding($classOrInterface)
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
     * @return boolean
     */
    public function has($classOrInterface)
    {
        return array_key_exists($classOrInterface, $this->bindings);
    }

    /**
     * Has instance.
     *
     * @param string $classOrInterface
     * @return boolean
     */
    public function hasInstance($classOrInterface)
    {
        return array_key_exists($classOrInterface, $this->instances) && is_object($this->instances[$classOrInterface]);
    }

    /**
     * Is shared.
     *
     * @param string $classOrInterface
     * @return boolean
     */
    public function isShared($classOrInterface)
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
     * @return boolean
     */
    public function isSingleton($classOrInterface)
    {
        return $this->isShared($classOrInterface);
    }

    /**
     * Forget.
     *
     * @param string $classOrInterface
     */
    public function forget($classOrInterface)
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
     * @param boolean $shared Default false.
     * @throws Exception
     */
    public function bind($classOrInterface, $instanceClass = null, $shared = false)
    {
        // Check if already bound.
        if ($this->has($classOrInterface)) {
            throw new Exception('Class or interface ' . $classOrInterface . ' already bound.');
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
     */
    public function singleton($classOrInterface, $instanceClass = null)
    {
        $this->bind($classOrInterface, $instanceClass, true);
    }

    /**
     * Instance.
     *
     * @param string $classOrInterface
     * @param object $object
     */
    public function instance($classOrInterface, $object)
    {
        $this->validateObject($classOrInterface, $object);
        if (!$this->has($classOrInterface)) {
            $this->singleton($classOrInterface, get_class($object));
        }
        $this->instances[$classOrInterface] = $object;
    }

    /**
     * Make.
     *
     * @param string $classOrInterface
     * @param array $resolveParameters Default [].
     * @return object
     */
    public function make($classOrInterface, array $resolveParameters = [])
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
        if ($isShared) {
            $this->instances[$classOrInterface] = $instance;
        }

        return $instance;
    }

    /**
     * Validate class or interface.
     *
     * @param string $classOrInterface
     * @throws Exception
     */
    private function validateClassOrInterface($classOrInterface)
    {
        if (!$this->classExists($classOrInterface)) {
            throw new Exception('Class or interface ' . $classOrInterface . ' does not exist.');
        }
    }

    /**
     * Validate instance class.
     *
     * @param string $classOrInterface
     * @param string $instanceClass
     * @throws Exception
     */
    private function validateInstanceClass($classOrInterface, $instanceClass)
    {
        // CHeck if concrete class exists.
        if (!class_exists($instanceClass)) {
            throw new Exception('Class ' . $instanceClass . ' does not exist.');
        }

        // If interface, check if instance class implements $classOrInterface.
        if (interface_exists($classOrInterface)) {
            if (!in_array($classOrInterface, class_implements($instanceClass))) {
                throw new Exception('Class ' . $instanceClass . ' does not implement ' . $classOrInterface);
            }
        }

        // If class, check if instance class extends $classOrInterface.
        if (class_exists($classOrInterface)) {
            if ($classOrInterface != $instanceClass && !in_array($classOrInterface, class_parents($instanceClass))) {
                throw new Exception('Class ' . $instanceClass . ' does not extend ' . $classOrInterface);
            }
        }
    }

    /**
     * Validate object.
     *
     * @param string $classOrInterface
     * @param object $object
     */
    private function validateObject($classOrInterface, $object)
    {
        $this->validateInstanceClass($classOrInterface, get_class($object));
    }

    /**
     * New instance.
     *
     * @param string $class
     * @param array $params
     * @return object
     * @throws \Exception
     */
    private function newInstance($class, array $params)
    {
        if (!class_exists($class)) {
            throw new \Exception('Class ' . $class . ' does not exist.');
        }
        $reflectionClass = new \ReflectionClass($class);
        return $reflectionClass->newInstanceArgs($params);
    }

    /**
     * Class exist.
     *
     * @param string $class
     * @return boolean
     */
    private function classExists($class)
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