<?php

declare(strict_types=1);

namespace CoRex\IoC;

use CoRex\IoC\Exceptions\IoCException;

class Resolver
{
    /**
     * Resolve parameters.
     *
     * @param string $class
     * @param mixed[] $resolveParameters Default [].
     * @param bool $resolveTypeHints Default true.
     * @return mixed[]
     * @throws IoCException
     */
    public static function resolveConstructor(
        string $class,
        array $resolveParameters = [],
        bool $resolveTypeHints = true
    ): array {
        $resolvedParameters = [];
        try {
            $reflectionClass = new \ReflectionClass($class);
            $constructor = $reflectionClass->getConstructor();
            if ($constructor !== null) {
                $parameters = $constructor->getParameters();
                if (count($parameters) > 0) {
                    $resolvedParameters = self::resolveParameters($parameters, $resolveParameters, $resolveTypeHints);
                }
            }
        } catch (\ReflectionException $e) {
            throw new IoCException($e->getMessage(), $e->getCode(), $e);
        }
        return $resolvedParameters;
    }

    /**
     * Resolve parameters.
     *
     * @param mixed[] $parameters
     * @param mixed[] $resolveParameters
     * @param bool $resolveTypeHints
     * @return mixed[]
     * @throws IoCException
     */
    private static function resolveParameters(
        array $parameters,
        array $resolveParameters,
        bool $resolveTypeHints
    ): array {
        $resolvedParameters = [];
        if (count($parameters) > 0) {
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                $hasDefaultValue = $parameter->isDefaultValueAvailable();
                $defaultValue = $hasDefaultValue ? $parameter->getDefaultValue() : null;

                // Extract type.
                $typeHint = null;
                $parameterReflectionClass = $parameter->getClass();
                if ($parameterReflectionClass !== null) {
                    $typeHint = $parameterReflectionClass->name;
                }

                if ($typeHint !== null) {
                    $classOrInterface = (string)$typeHint;
                    if ($resolveTypeHints) {
                        $value = Container::getInstance()->make($classOrInterface);
                    } else {
                        $value = $classOrInterface;
                    }
                } elseif (array_key_exists($name, $resolveParameters)) {
                    $value = $resolveParameters[$name];
                } else {
                    if ($hasDefaultValue) {
                        $value = $defaultValue;
                    } else {
                        throw new IoCException('Parameter ' . $name . ' not found.');
                    }
                }
                $resolvedParameters[] = $value;
            }
        }
        return $resolvedParameters;
    }
}