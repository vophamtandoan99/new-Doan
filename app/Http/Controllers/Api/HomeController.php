<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\HomeRepository;
use App\Http\Resources\product\ProductResource;
use App\Http\Resources\product\ProductCollection;

class HomeController extends Controller
{
    private $homeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        $this->homeRepository = $homeRepository;
    }
    public function getProductSupplier($id)
    {
        return new ProductCollection($this->homeRepository->getSupplier($id));
    }
    public function getProductCategory($id)
    {
        return new ProductCollection($this->homeRepository->getCategory($id));
    }
}
