<?php

declare(strict_types=1);

namespace CoRex\IoC;

use CoRex\IoC\Exceptions\IoCException;

class Binding
{
    /** @var string */
    private $classOrInterface;

    /** @var bool */
    private $isInterface;

    /** @var string */
    private $instanceClass;

    /** @var bool */
    private $shared;

    /**
     * Binding.
     *
     * @param string $classOrInterface
     * @param string $instanceClass
     * @param bool $shared
     * @throws IoCException
     */
    public function __construct(string $classOrInterface, string $instanceClass, bool $shared)
    {
        if (!$this->classExists($classOrInterface)) {
            throw new IoCException('Class ' . (string)$classOrInterface . ' does not exist.');
        }
        if (!$this->classExists($instanceClass)) {
            throw new IoCException('Class ' . (string)$instanceClass . ' does not exist.');
        }
        $this->classOrInterface = $classOrInterface;
        $this->isInterface = interface_exists($classOrInterface);
        $this->instanceClass = $instanceClass;
        $this->shared = (bool)$shared;
    }

    /**
     * Set shared.
     */
    public function setShared(): void
    {
        $this->shared = true;
    }

    /**
     * Is shared.
     *
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Get instance class.
     *
     * @return string
     */
    public function getInstanceClass(): string
    {
        return $this->instanceClass;
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