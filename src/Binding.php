<?php

namespace CoRex\IoC;

class Binding
{
    private $classOrInterface;
    private $isInterface;
    private $instanceClass;
    private $shared;

    /**
     * Binding constructor.
     * @param string $classOrInterface
     * @param string $instanceClass
     * @param boolean $shared
     * @throws Exception
     */
    public function __construct($classOrInterface, $instanceClass, $shared)
    {
        if (!$this->classExists($classOrInterface)) {
            throw new Exception('Class ' . (string)$classOrInterface . ' does not exist.');
        }
        if (!$this->classExists($instanceClass)) {
            throw new Exception('Class ' . (string)$instanceClass . ' does not exist.');
        }
        $this->classOrInterface = $classOrInterface;
        $this->isInterface = interface_exists($classOrInterface);
        $this->instanceClass = $instanceClass;
        $this->shared = (boolean)$shared;
    }

    /**
     * Is shared.
     *
     * @return boolean
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * Get instance class.
     *
     * @return string
     */
    public function getInstanceClass()
    {
        return $this->instanceClass;
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