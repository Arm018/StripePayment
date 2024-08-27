<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    protected $fillable =
        [
            'product_id',
            'total',
            'stripe_session_id',
            'status'
        ];


    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function getStatusNameAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILED => 'Failed',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

}
