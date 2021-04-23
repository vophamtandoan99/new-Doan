<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Http\Resources\product\ProductResource;
use App\Http\Resources\product\ProductCollection;
use App\Http\Resources\product\SizeCollection;
use App\Http\Resources\product\ColorCollection;

class HomeController extends Controller
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function getSize()
    {
        return new SizeCollection($this->productRepository->getSize());
    }
    public function getColor()
    {
        return new ColorCollection($this->productRepository->getColor());
    }
}
