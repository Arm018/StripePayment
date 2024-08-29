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

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_SUCCESS => 'Success',
        self::STATUS_FAILED => 'Failed',
    ];
    protected $fillable =
        [
            'total',
            'status',
            'created_at',
            'updated_at',
        ];


    public function payments()
    {
        return $this->hasMany(OrderPayment::class, 'order_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }


    public function getStatusName()
    {
        return self::STATUSES[$this->status];
    }

    public function allPaymentsSuccessful()
    {
        return $this->payments->every(function ($payment) {
            return $payment->status === OrderPayment::STATUS_PAID;
        });
    }

    public function updateStatus()
    {
        if ($this->allPaymentsSuccessful()) {
            $this->update(['status' => self::STATUS_SUCCESS]);
        }
    }

}
