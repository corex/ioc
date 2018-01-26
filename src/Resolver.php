<?php

namespace CoRex\IoC;

class Resolver
{
    /**
     * Resolve parameters.
     *
     * @param string $class
     * @param array $resolveParameters Default [].
     * @param boolean $resolveTypeHints Default true.
     * @return array
     * @throws Exception
     */
    public static function resolveConstructor($class, array $resolveParameters = [], $resolveTypeHints = true)
    {
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
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $resolvedParameters;
    }

    /**
     * Resolve parameters.
     *
     * @param array $parameters
     * @param array $resolveParameters
     * @param boolean $resolveTypeHints
     * @return array
     * @throws Exception
     */
    private static function resolveParameters(array $parameters, array $resolveParameters, $resolveTypeHints)
    {
        $resolvedParameters = [];
        if (count($parameters) > 0) {
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                $typeHint = $parameter->getType();
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
                    throw new Exception('Parameter ' . $name . ' not found.');
                }
                $resolvedParameters[] = $value;
            }
        }
        return $resolvedParameters;
    }
}