<?php

namespace Tests\Units\Generators;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use SethPhat\EloquentDocs\Services\Generators\ScopesGenerator;

class ScopesTestModel1 extends Model {
    public function scopePopular($query)
    {
        $query->where('votes', '>', 100);
    }
    public function scopeActive($query)
    {
        $query->where('status', '=', 'active');
    }
}

class ScopesTestModel2 extends Model {
    public function nonScopeMethod()
    {
        return 'This is not a scope';
    }
}

class ScopesGeneratorTest extends TestCase
{
    public function testGenerateIncludesLocalScopes(): void
    {

        $model = new ScopesTestModel1();

        $generator = new ScopesGenerator();
        $phpDoc = $generator->generate($model, []);

        print("phpdoc:\n$phpDoc");

        $this->assertStringContainsString('=== Scopes ===', $phpDoc);
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> popular()', $phpDoc);
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> active()', $phpDoc);
    }

    public function testGenerateExcludesNonScopeMethods(): void
    {
        $model = new ScopesTestModel2();

        $generator = new ScopesGenerator();
        $phpDoc = $generator->generate($model, []);

        $this->assertStringNotContainsString('=== Scopes ===', $phpDoc);
        $this->assertStringNotContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel2> nonScopeMethod()', $phpDoc);
    }
}
