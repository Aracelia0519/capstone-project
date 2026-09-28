<?php

namespace App\Support\Inventory\Exceptions;

use RuntimeException;

/**
 * Thrown when a requested quantity cannot be covered.
 *
 * Deliberately distinguishes "the warehouse does not have it" from "what it has
 * has already expired", because the two need different fixes from the user:
 * the first is a stock problem, the second is an archiving problem.
 */
class InsufficientStock extends RuntimeException
{
    public static function unprocurable(
        string $productName,
        int $available,
        int $requested
    ): self {
        return new self(sprintf(
            'Only %d unit(s) of "%s" can be procured right now (%d requested). '
            . 'The rest is either already reserved or past its expiration date.',
            $available,
            $productName,
            $requested
        ));
    }

    public static function unavailable(string $productName, int $requested): self
    {
        return new self(sprintf(
            '"%s" cannot be procured. None of its remaining stock is within its expiration date. '
            . 'Ask the supplier to archive the expired batches and add fresh stock.',
            $productName
        ));
    }
}
