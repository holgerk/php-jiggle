<?php

class Jiggle {

    private $unresolvedDeps = array();
    private $resolvedDeps = array();
    private $trailOfCurrentlyResolvingDeps = array();
    private $resolver = null;

    /**
     * Create a singleton factory for the given class
     *
     * @param  string $class the class which should be instantiated
     * @return callable
     */
    public function singleton($class) {
        $self = $this;
        return function() use($self, $class) {
            return $self->create($class);
        };
    }

    /**
     * Create an object of the given class
     *
     * If the class constructor has arguments they are injected from the current jiggle instance.
     *
     * @param  string $class the class to instantiate
     * @return object the newly created instance
     */
    public function create($class) {
        $reflectionClass = new ReflectionClass($class);
        $reflectionMethod = $reflectionClass->getConstructor();
        if (!$reflectionMethod) {
            return new $class;
        }
        $params = &$this->fetchDepsFromSignature($reflectionMethod);
        return $reflectionClass->newInstanceArgs($params);
    }

    /**
     * Executes the given callable
     *
     * If the callable has arguments they are injected from the current jiggle instance.
     *
     * @param  callable $callable the callable to invoke
     * @param  array $overloadedDeps optional additional or overloaded dependencies
     * @return mixed return value of the invoked callable
     */
    public function inject($callable, $overloadedDeps = array()) {
        $reflection = new ReflectionFunction($callable);
        $params = &$this->fetchDepsFromSignature($reflection, $overloadedDeps);
        return call_user_func_array($callable, $params);
    }

    /**
     * Replace an existing dependency
     *
     * Usefull if one would like to mock some part of a bigger system or for clients to replace an
     * unwanted implementation.
     *
     * @param  string $name  of the dependency to replace
     * @param  mixed  $value replacement implementation
     */
    public function replace($name, $value) {
        if (!isset($this->unresolvedDeps[$name])) {
            throw new Exception("Dependency does not exist: $name!");
        }
        if (isset($this->resolvedDeps[$name])) {
            throw new Exception("Could replace resolved Dependency: $name!");
        }
        $this->unresolvedDeps[$name] = &$value;
    }

    /**
     * Let one provide a callable which is called if a dependency is not resolvable
     *
     * If the provided resolver can resolve the dependency it should simply return it.
     * If the dependency could not resolved the resolver needs to throw an exception,
     * otherwise the dependency would be resolved to null.
     *
     * @param  callable $callable the resolver callabe
     */
    public function resolver($callable) {
        $this->resolver = $callable;
    }

    /**
     * @deprecated
     */
    public function createFactory($class) {
        throw new Exception('Deprecated: createFactory is renamed to singleton!');
    }

    public function __set($name, $value) {
        if (isset($this->unresolvedDeps[$name])) {
            throw new Exception("Dependency allready exists: $name!");
        }
        $this->unresolvedDeps[$name] = $value;
    }

    public function &__get($name) {
        $this->resolveDep($name);
        $dep = &$this->resolvedDeps[$name];
        return $dep;
    }

    public function __isset($name) {
        return isset($this->unresolvedDeps[$name]);
    }

    public function __call($name, $args) {
        $function = $this->__get($name);
        return call_user_func_array($function, $args);
    }

    private function resolveDep($name) {
        if (in_array($name, $this->trailOfCurrentlyResolvingDeps)) {
            $path = implode(' -> ', $this->trailOfCurrentlyResolvingDeps);
            throw new Exception("Circular dependencies: $path -> $name!");
        }

        if (isset($this->resolvedDeps[$name])) {
            return;
        } else if (!isset($this->unresolvedDeps[$name])) {
            if (isset($this->resolver)) {
                $this->resolvedDeps[$name] = call_user_func($this->resolver, $name);
            } else {
                throw new Exception("Dependency is missing: $name!");
            }
        } else {
            $this->trailOfCurrentlyResolvingDeps[] = $name;

            $unresolved = &$this->unresolvedDeps[$name];
            $resolved = null;
            if (is_callable($unresolved)) {
                $reflection = new ReflectionFunction($unresolved);
                $params = &$this->fetchDepsFromSignature($reflection);
                $resolved = call_user_func_array($unresolved, $params);
            } else {
                $resolved = &$unresolved;
            }
            $this->resolvedDeps[$name] = &$resolved;

            array_pop($this->trailOfCurrentlyResolvingDeps);
        }
    }

    private function &fetchDepsFromSignature($reflectionFunction, $overloadedDeps = array()) {
        $params = array();
        foreach ($reflectionFunction->getParameters() as $param) {
            if (array_key_exists($param->getName(), $overloadedDeps)) {
                $params[] = $overloadedDeps[$param->getName()];
            } else {
                $params[] = &$this->__get($param->getName());
            }
        }
        return $params;
    }

}