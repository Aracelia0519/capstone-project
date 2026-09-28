<?php

namespace App\Http\Requests\Supplier;

use App\Support\Inventory\BatchRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Shared rules for creating and editing a supplier material.
 *
 * The expiration rules cannot be expressed as a plain rule string because they
 * depend on the chosen category, so they run in {@see withValidator()} and read
 * the same {@see BatchRules} the service and the frontend use. One source of
 * truth, three consumers.
 */
abstract class RawMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category'    => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'name'        => 'required|string|max:255',
            'sku_code'    => 'nullable|string|max:255',
            'size'        => 'required|string|max:255',
            'weight'      => 'required|numeric|min:0',
            'color_code'  => 'nullable|string|max:255',
            'price'       => 'required|numeric|min:0',
            'min_order'   => 'nullable|integer|min:1',
            'max_order'   => 'nullable|integer|gte:min_order',
            'description' => 'nullable|string',

            // Batch fields
            'quantity'            => 'nullable|integer|min:1',
            'expiration_date'     => 'nullable|date_format:Y-m-d',
            'batch_code'          => 'nullable|string|max:64',
            'minimum_stock_level' => 'nullable|integer|min:0',

            'image' => 'nullable|image|max:2048',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'quantity'        => 'quantity',
            'expiration_date' => 'expiration date',
            'category'        => 'category',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Enter how many units you are adding.',
            'quantity.min'      => 'Quantity must be at least 1.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $category = $this->input('category');
            $date     = $this->input('expiration_date');

            $service = app(\App\Support\Inventory\BatchInventoryService::class);
            $error   = $service->validateExpiration($category, $date);

            if ($error !== null) {
                // Deliberately reported against `expiration_date` so the frontend can
                // highlight the right field.
                $validator->errors()->add('expiration_date', $error);

                return;
            }

            // The very first batch must be at least 1 unit; later restocks are
            // validated by RestockRawMaterialRequest.
            if ($this->isCreating() && ! $this->filled('quantity')) {
                $validator->errors()->add('quantity', 'Enter how many units you are adding.');
            }
        });
    }

    protected function isCreating(): bool
    {
        return ! $this->route('id');
    }

    /**
     * Everything the controller should persist, minus the file upload.
     *
     * `quantity`, `expiration_date` and `batch_code` are deliberately dropped:
     * they describe a DELIVERY, not the product, and on this table they are cached
     * rollups owned by the service layer. Writing them from a form would let a
     * plain edit of the price silently overwrite stock that the batches still
     * account for.
     *
     * @return array<string, mixed>
     */
    public function materialAttributes(): array
    {
        return $this->safe()->except([
            'image',
            'expiration_date',
            'batch_code',
            'quantity',
        ]);
    }
}
