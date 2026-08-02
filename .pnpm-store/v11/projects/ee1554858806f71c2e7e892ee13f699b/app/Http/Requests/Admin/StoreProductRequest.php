<?php

namespace App\Http\Requests\Admin;

class StoreProductRequest extends SaveProductRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }
}
