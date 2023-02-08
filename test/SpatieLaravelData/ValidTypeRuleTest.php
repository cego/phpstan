<?php

namespace Test\SpatieLaravelData;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Test\Samples\InvalidScalarSpatieLaravelData;
use Test\Samples\InvalidComplexSpatieLaravelData;
use Cego\phpstan\SpatieLaravelData\Rules\ValidTypeRule;
use Cego\phpstan\SpatieLaravelData\Collectors\CastCollector;
use Cego\phpstan\SpatieLaravelData\Collectors\FromCollector;
use Cego\phpstan\SpatieLaravelData\Collectors\ConstructorCollector;

class ValidTypeRuleTest extends RuleTestCase
{
    /**
     * @phpstan-return TRule
     */
    protected function getRule(): Rule
    {
        return new ValidTypeRule();
    }

    /**
     * @return array
     */
    protected function getCollectors(): array
    {
        return [
            new CastCollector(),
            new ConstructorCollector(),
            new FromCollector(),
        ];
    }

    /** @test */
    public function it_returns_no_errors_for_valid_data(): void
    {
        $this->analyse([__DIR__ . '/../Samples/ValidSpatieLaravelData.php'], []);
    }

    /** @test */
    public function it_returns_no_errors_for_valid_complex_data(): void
    {
        $this->analyse([__DIR__ . '/../Samples/ValidComplexSpatieLaravelData.php'], []);
    }

    /** @test */
    public function it_returns_errors_for_invalid_scalar_data(): void
    {
        $this->analyse([__DIR__ . '/../Samples/InvalidScalarSpatieLaravelData.php'], [
            $this->expectError(21, 'stringProperty', InvalidScalarSpatieLaravelData::class, 'string', 'null'),
            $this->expectError(21, 'intProperty', InvalidScalarSpatieLaravelData::class, 'int', 'float'),
            $this->expectError(21, 'booleanProperty', InvalidScalarSpatieLaravelData::class, 'bool', 'array'),
            $this->expectError(21, 'floatProperty', InvalidScalarSpatieLaravelData::class, 'float', 'string'),
        ]);
    }

    /** @test */
    public function it_returns_errors_for_invalid_complex_data(): void
    {
        $this->analyse([__DIR__ . '/../Samples/InvalidComplexSpatieLaravelData.php'], [
            $this->expectError(21, 'nullableMarkStringProperty', InvalidComplexSpatieLaravelData::class, 'null|string', 'int'),
            $this->expectError(21, 'nullableTypeStringProperty', InvalidComplexSpatieLaravelData::class, 'null|string', 'float'),
            $this->expectError(21, 'nullableMarkStringProperty', InvalidComplexSpatieLaravelData::class, 'null|string', 'array'),
            $this->expectError(21, 'nullableTypeStringProperty', InvalidComplexSpatieLaravelData::class, 'null|string', 'array'),
            $this->expectError(21, 'intersectionProperty', InvalidComplexSpatieLaravelData::class, '\Spatie\LaravelData\Casts\Cast&\Stringable', 'stdClass'),
        ]);
    }

    /** @test */
    public function it_ignores_potential_problems_for_objects_with_casts(): void
    {
        $this->analyse([
            __DIR__ . '/../Samples/CastedSpatieLaravelData.php',
            __DIR__ . '/../Samples/Casts/BackedEnumCast.php',
            __DIR__ . '/../Samples/Casts/UncastableSpatieDataCast.php',
        ], []);
    }

    private function expectError(int $line, string $property, string $class, string $expectedType, string $actualType): array
    {
        return [
            ValidTypeRule::getErrorMessage($property, $class, $expectedType, $actualType),
            $line,
        ];
    }
}
