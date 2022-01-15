<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderItem extends Model
{
    //belongs to Order
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Order');
    }
}
