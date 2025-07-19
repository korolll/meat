<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getAllProducts()
    {
        return Product::all(['id', 'name', 'description', 'price', 'category', 'in_stock']);
    }
}
