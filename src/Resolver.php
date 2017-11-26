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
        $result = [];
        $reflectionClass = new \ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            $constructorParameters = $constructor->getParameters();
            if (count($constructorParameters) > 0) {
                foreach ($constructorParameters as $constructorParameter) {
                    $name = $constructorParameter->getName();
                    $typeHint = $constructorParameter->getType();
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
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
}