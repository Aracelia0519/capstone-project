<?php

namespace App\Support\Inventory\Exceptions;

use RuntimeException;

/**
 * Thrown when an incoming batch has a missing or too-soon expiration date.
 *
 * A FormRequest turns this into a 422 with a readable message, so the supplier
 * sees exactly which rule was broken instead of a generic failure.
 */
class InvalidExpirationDate extends RuntimeException
{
    public static function forCategory(?string $category): self
    {
        return new self(sprintf(
            'An expiration date is required for "%s". Tools, Accessories and Packaging are the only categories where it may be left blank.',
            $category ?? 'this product'
        ));
    }
}
