<?php

namespace App\Http\Requests\Admin;

class StoreProductCategoryRequest extends SaveProductCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }
}
