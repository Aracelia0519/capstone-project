<?php

namespace App\Http\Requests\Supplier;

/**
 * Creating a brand new supplier material.
 *
 * Exists purely so the controller can type-hint a concrete class —
 * RawMaterialRequest is abstract, and Laravel's container cannot instantiate an
 * abstract type-hint, which surfaces as
 * "Target [...RawMaterialRequest] is not instantiable."
 *
 * The opening delivery must state its quantity, because a product with no
 * batches has nothing to sell.
 */
class StoreRawMaterialRequest extends RawMaterialRequest
{
    protected function isCreating(): bool
    {
        return true;
    }
}
