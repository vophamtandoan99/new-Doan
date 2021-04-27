<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\BaseResource;
use App\Http\Resources\product\ProductSizeColorResource;
use App\Repositories\CustomerRepository;
use App\Repositories\BillRepository;
use App\Http\Requests\BillRequest;
use App\Http\Resources\bill\BillResource;
use App\Http\Resources\bill\StatisticalResource;
use App\Http\Resources\bill\BillDetailResource;
use App\Http\Resources\bill\BillDetailCollection;
use App\Http\Resources\bill\BillCollection;
use App\Repositories\ProductRepository;
use App\Http\Resources\customer\CustomerCollection;
use App\Http\Resources\customer\CustomerResource;

class BillController
{
    private $billRepository;
    private $customerRepository;
    private $productRepository;

    public function __construct(BillRepository $billRepository, CustomerRepository $customerRepository, ProductRepository $productRepository)
    {
        $this->billRepository = $billRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
    }
    public function search(BillRequest $request)
    {
        return new BillCollection($this->billRepository->search($request->searchFilter()));
    }
    public function store(Request $request, CustomerRequest $customerRequest, BillRequest $billRequest)
    {
        $items = session('cart');
        if(!empty($items)){
                $totalPrice      = 0;
                $totalQuantity   = 0;
                foreach($items as $rowCart){
                    $totalPrice      += $rowCart['price']*$rowCart['quantity'];
                    $totalQuantity   += $rowCart['quantity'];
                }
                $cart = [
                    'items' => $items,
                    'total_price'=> $totalPrice,
                    'total_quantity' => $totalQuantity
                ];
                $customer = new CustomerResource($this->customerRepository->store($customerRequest->storeFilter()));
                //return $customer;
                //dd($customer);
                if($customer ==true){
                    $bill = new BillResource($this->billRepository->store($billRequest->storeFilter(), $customer->id, $cart));
                    if($bill == true){
                        foreach($cart['items'] as $rowCart){
                            $PSCdata = $this->billRepository->showPSC($rowCart);
                            $billDetail = new BaseResource($this->billRepository->storeBillDetail($bill->id, $PSCdata, $rowCart));   
                            $this->productRepository->updateAmountProduct($rowCart);
                        }
                        $showBillDetail = $this->billRepository->showBillDetail($bill->id);
                        $request->session()->forget('cart');
                        //dd($bill->id);
                        return [
                            'customer' => $customer,
                            'bill' => $bill,
                            'billDetail' => $showBillDetail
                        ];
                    }
                }
        }
    }
    public function show($id)
    {
        $bill = new BillResource($this->billRepository->showBill($id));
        $billDetail = new BillDetailCollection($this->billRepository->showBillDetail($id));
         return [$bill, $billDetail];
    }
    public function showBillDetail($id){
        $bill = new BillResource($this->billRepository->showBill($id));
        $customerInfo = $this->customerRepository->show($bill[0]->customer_id);
        $billDetail = $this->billRepository->showBillDetail($id);
        return [$customerInfo, $billDetail];
    }
    public function update($id)
    {
        $bill = $this->billRepository->show($id);
        if($bill->status == 1){
            return new BaseResource($this->billRepository->update($id));
        }else{
            return new BaseResource($this->billRepository->updateStatus($id));
        } 
    }
    public function destroy($id, Request $request)
    {
        $bill = $this->billRepository->show($id);
        $destroy = new BaseResource($this->billRepository->destroy($id, $request->session()->put('bill', $bill->customer_id)));
        $request->session()->forget('bill');
        return $destroy;
    }
    public function statistical()
    {
        return response()->json($this->billRepository->statistical(), 200);
    }
}
