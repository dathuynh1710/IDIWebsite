<?php

namespace App\Http\Requests\Admin;

class UpdateProductCategoryRequest extends SaveProductCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }
}
