<?php

namespace App\Http\Requests\Supplier;

/**
 * Editing an existing supplier material's details.
 *
 * The batch rules are inherited, and they still apply — but only to a date the
 * caller actually supplied. Editing a price must not fail because the product
 * was created years ago under looser rules, and must never be a back door for
 * changing stock: `quantity` is dropped by
 * {@see RawMaterialRequest::materialAttributes()} and the expiration date is
 * only meaningful on a restock.
 */
class UpdateRawMaterialRequest extends RawMaterialRequest
{
    protected function isCreating(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // The product's own stock is a rollup of its batches. Accepting a
        // quantity here would let a form silently overwrite stock that the lots
        // on record still account for, so the field is not accepted at all.
        unset($rules['quantity']);

        return $rules;
    }
}
