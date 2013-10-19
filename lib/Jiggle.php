<?php

class Jiggle {

    private $unresolvedDeps = array();
    private $resolvedDeps = array();

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

    public function __set($name, $value) {
        if (isset($this->unresolvedDeps[$name])) {
            throw new Exception("Dependency allready exists: $name!");
        }
        $this->unresolvedDeps[$name] = $value;
    }

    public function &__get($name) {
        $this->resolveDep($name);
        return $this->resolvedDeps[$name];
    }

    private function resolveDep($name) {
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
                $resolved = &call_user_func_array($unresolved, $params);
            } else {
                $resolved = &$unresolved;
            }
            $this->resolvedDeps[$name] = &$resolved;
        }
    }

    private function &fetchDepsFromSignature ($reflectionFunction) {
        $params = array();
        foreach ($reflectionFunction->getParameters() as $param) {
            $params[] = &$this->{$param->getName()};
        }
        return $params;
    }

}