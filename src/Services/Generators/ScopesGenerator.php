<?php

namespace SethPhat\EloquentDocs\Services\Generators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

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
                $args="";
                $i = -1;
                $args = join(", ", array_filter(array_map(function ($param) use (&$i) {
                    $type = $param->getType();
                    $typeDoc = "";
                    $i += 1;
                    print("$i: $param\n  $type\n");
                    if ($type) {
                        if ($type instanceof ReflectionNamedType && $type->getName() === Builder::class) {
                            return null;
                        }
                        $typeDoc = "$type ";
                    } else if ($i === 0) {
                        // First argument is the query builder
                        return null;
                    }
                    if ($param->isPassedByReference()) {
                        $typeDoc .= "&";
                    }
                    if ($param->isVariadic()) {
                        $typeDoc .= "...";
                    }
                    return $typeDoc . '$' . $param->getName();
                }, $method->getParameters()), fn ($item) => !empty($item)));
                $phpDoc .= " * @method static \\Illuminate\\Database\\Eloquent\\Builder<{$className}> {$scopeName}($args)\n";
            }
        }

        if (!$phpDoc) {
          return "";
        }

        return " * === Scopes ===\n$phpDoc";
    }

    private function isLocalScope(ReflectionMethod $method): bool
    {
        $methodName = $method->getName();
        return str_starts_with($methodName, 'scope') && strlen($methodName) > 5;
    }
}
