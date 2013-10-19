<?php

class Jiggle {

    private $unresolvedDeps = array();
    private $resolvedDeps = array();

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
                $params = array();
                foreach ($reflection->getParameters() as $param) {
                    $params[] = &$this->{$param->getName()};
                }
                $resolved = &call_user_func_array($unresolved, $params);
            } else {
                $resolved = &$unresolved;
            }
            $this->resolvedDeps[$name] = &$resolved;
        }
    }

}