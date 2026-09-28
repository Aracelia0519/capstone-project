<?php

namespace App\Http\Requests\Supplier;

use App\Support\Inventory\BatchRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Adding NEW stock to a product that already exists.
 *
 * Separate from RawMaterialRequest because the two cases have different rules:
 * creating a product requires an initial quantity, whereas restocking requires one
 * too but must not touch the product's price, name, category or any other field.
 */
class RestockRawMaterialRequest extends RawMaterialRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // A restock is nothing but units plus a deadline.
            'quantity'        => 'required|integer|min:1',
            'expiration_date' => 'nullable|date_format:Y-m-d',
            'batch_code'      => 'nullable|string|max:64',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // The category decides whether the date may be blank, and this
            // request does not carry a category — it has to come from the
            // product being topped up.
            $category = $this->targetMaterial()?->category;

            // A restock aimed at a product that does not exist is a routing
            // error, but reporting it here keeps the failure attached to the
            // form the user actually submitted.
            if ($category === null) {
                $validator->errors()->add('quantity', 'That material no longer exists.');

                return;
            }

            $error = app(\App\Support\Inventory\BatchInventoryService::class)
                ->validateExpiration($category, $this->input('expiration_date'));

            if ($error !== null) {
                $validator->errors()->add('expiration_date', $error);
            }
        });
    }

    /**
     * The product this restock targets.
     *
     * The route is `raw-materials/{id}/restock` and this group does not use
     * implicit model binding, so the bound parameter arrives as a raw id.
     * Reading `->category` straight off it would have yielded null and forced
     * an expiration date onto every tool, accessory and packaging restock —
     * exactly the case where the date is optional.
     */
    private function targetMaterial(): ?\App\Models\Supplier\SupplierRawMaterial
    {
        $param = $this->route('material') ?? $this->route('id');

        if ($param instanceof \App\Models\Supplier\SupplierRawMaterial) {
            return $param;
        }

        if (! is_numeric($param)) {
            return null;
        }

        return \App\Models\Supplier\SupplierRawMaterial::where('user_id', $this->user()?->id)
            ->find((int) $param);
    }

    protected function isCreating(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Enter how many units you are adding.',
        ];
    }
}
