<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionPurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $arr = explode('/', $this->data_request->sn);
        return [
            'customer_id' => $this->customer_id,
            'code' => $this->code,
            'msisdn' => $this->msisdn,
            'request_id' => $this->request_id,
            'rc' => $this->rc,
            'trxid' => $this->trxid,
            'price' => $this->price,
            'balance' => $this->balance,
            'sn' => $this->sn,
            'message' => $this->message,
            'payment_method' => $this->payment_method,
            'data_request' => $arr,
            'customer' => Customer::find($this->customer_id)
        ];
    }
}
