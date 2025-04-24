<?php

namespace Tests\Units\Generators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use SethPhat\EloquentDocs\Services\Generators\ScopesGenerator;

class ScopesTestModel1 extends Model {
    public function scopePopular1(Builder $query, ...$args)
    {
        $query->where('votes', '>', 100);
    }

    public function scopePopular2(Builder $query, array ...$args)
    {
        $query->where('votes', '>', 100);
    }

    public function scopeUnpopular(Builder $query, ?int $votes = null)
    {
        $query->where('votes', '<=', $votes);
    }

    public function scopeInactive(Builder $query, bool &$foo)
    {
      $query->where('status', '!=', 'active');
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
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> popular1(...$args)', $phpDoc);
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> popular2(array ...$args)', $phpDoc);
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> unpopular(?int $votes)', $phpDoc);
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> active()', $phpDoc);
        $this->assertStringContainsString('@method static \Illuminate\Database\Eloquent\Builder<ScopesTestModel1> inactive(bool &$foo)', $phpDoc);
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
