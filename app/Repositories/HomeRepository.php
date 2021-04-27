<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\UserUnauthorizedException;
use Config;

class HomeRepository
{
    public function getSupplier($id)
    {
        return Product::wheresupplier_id($id)
        ->orderBy('sale', 'desc')
        ->paginate();
    }
    public function getCategory($id)
    {
        return Product::wherecategory_id($id)
        ->orderBy('sale', 'desc')
        ->paginate();
    }
}
