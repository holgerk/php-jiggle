<?php

class Jiggle {

    private $unresolvedDeps = array();
    private $resolvedDeps = array();
    private $trailOfCurrentlyResolvingDeps = array();

    public function create($class) {
        $reflectionClass = new ReflectionClass($class);
        $reflectionMethod = $reflectionClass->getConstructor();
        if (!$reflectionMethod) {
            return new $class;
        }
        $params = &$this->fetchDepsFromSignature($reflectionMethod);
        return $reflectionClass->newInstanceArgs($params);
    }

    public function createFactory($class) {
        $self = $this;
        return function() use($self, $class) {
            return $self->create($class);
        };
    }

    public function replace($name, $value) {
        if (!isset($this->unresolvedDeps[$name])) {
            throw new Exception("Dependency does not exist: $name!");
        }
        if (isset($this->resolvedDeps[$name])) {
            throw new Exception("Could replace resolved Dependency: $name!");
        }
        $this->unresolvedDeps[$name] = &$value;
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

    public function __call($name, $args) {
        $function = $this->__get($name);
        return call_user_func_array($function, $args);
    }

    private function resolveDep($name) {
        if (in_array($name, $this->trailOfCurrentlyResolvingDeps)) {
            $path = implode(' -> ', $this->trailOfCurrentlyResolvingDeps);
            throw new Exception("Circular dependencies: $path -> $name!");
        }
        $this->trailOfCurrentlyResolvingDeps[] = $name;

        if (isset($this->resolvedDeps[$name])) {
            return;
        } else if (!isset($this->unresolvedDeps[$name])) {
            throw new Exception("Dependency is missing: $name!");
        } else {
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
        }

        array_pop($this->trailOfCurrentlyResolvingDeps);
    }

    private function &fetchDepsFromSignature ($reflectionFunction) {
        $params = array();
        foreach ($reflectionFunction->getParameters() as $param) {
            $params[] = &$this->__get($param->getName());
        }
        return $params;
    }

}