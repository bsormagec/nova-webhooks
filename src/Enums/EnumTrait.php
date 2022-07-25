<?php

namespace Pagzi\NovaWebhooks\Enums;

use Illuminate\Support\Str;
use ReflectionClass;

trait EnumTrait
{

    /**
     * The value of one of the enum members.
     *
     * @var mixed
     */
    public $value;

    /**
     * The key of one of the enum members.
     *
     * @var string
     */
    public $key;


    public function __construct($enumValue)
    {
        if (! static::hasValue($enumValue)) {
            throw new \RuntimeException("This value is not part of the enum");
        }

        $this->value = $enumValue;
        $this->key = static::getKey($enumValue);
    }

    public static function init(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }

    public static function names(): array
    {
        return array_keys(static::init());
    }

    public static function values(): array
    {
        return array_values(static::init());
    }

    public static function readableNames(): array
    {
        return array_map(static fn($item) => Str::of($item)->explode('_')->map(fn($item) => Str::of($item)->lower()->ucfirst()->__toString())->implode(' '), static::names());
    }

    public static function implode(): string
    {
        return implode(', ', static::readableNames());
    }

    public static function toArray(): array
    {
        $values = static::values();
        $names = static::readableNames();
        return array_combine($values, $names);
    }

    public static function hasValue($value): bool
    {
        return in_array($value, static::values(), true);
    }

    /**
     * Make a new instance from an enum value.
     *
     * @param  mixed  $enumValue
     * @return static
     */
    public static function fromValue($enumValue): self
    {
        if ($enumValue instanceof static) {
            return $enumValue;
        }

        return new static($enumValue);
    }

    public function is($enumValue): bool
    {
        if ($enumValue instanceof static) {
            return $this->value === $enumValue->value;
        }

        return $this->value === $enumValue;
    }

    public static function getKey($value): string
    {
        return array_search($value, static::values(), true);
    }

}
