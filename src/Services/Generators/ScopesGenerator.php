<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;

class ScopesGenerator implements PhpDocGeneratorContract
{
    public function generate(Model $model, array $options = []): string
    {
        $phpDoc = "";
        $className = class_basename($model);
        $reflection = new ReflectionClass($model);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($this->isLocalScope($method)) {
                // Remove 'scope' from method name and convert to camelCase
                $scopeName = lcfirst(substr($method->getName(), 5));
                $phpDoc .= " * @method static \\Illuminate\\Database\\Eloquent\\Builder<{$className}> {$scopeName}()\n";
            }
        }

        if (!$phpDoc) {
          return "";
        }

        return "\n * === Scopes ===\n$phpDoc";
    }

    private function isLocalScope(ReflectionMethod $method): bool
    {
        $methodName = $method->getName();
        return str_starts_with($methodName, 'scope') && strlen($methodName) > 5;
    }
}
