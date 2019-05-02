<?php

namespace App\Http\Resources\company;

use Illuminate\Http\Resources\Json\ResourceCollection;

class companyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            "name" => $this->name,
            "address" => $this->address,
            "phoneNumber" => $this->phone,
        ];
    }
}
