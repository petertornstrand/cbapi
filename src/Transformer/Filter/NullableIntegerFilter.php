<?php

namespace App\Transformer\Filter;

/**
 * Filter.
 */
final class NullableIntegerFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return int|null The value
     */
    public function __invoke($value)
    {
        if (
            $value instanceof \SimpleXMLElement
            && empty((string)$value)
            && (string)$value->attributes()?->nil === 'true'
        ) {
            return null;
        }
        return $value === '' ? null : (int)$value;
    }
}
