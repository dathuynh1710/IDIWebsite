<?php

namespace App\Http\Requests\Admin;

class UpdateProductRequest extends SaveProductRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }
}
