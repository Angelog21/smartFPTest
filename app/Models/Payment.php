<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;


class Payment extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'origin_id',
        'receiver_id',
        'payment_ms',
        'client_name',
        'cpf',
        'description',
        'amount',
        'status',
        'payment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $keyType = 'string';

    public function origin() {
        return $this->belongsTo(User::class,'origin_id');
    }

    public function receiver() {
        return $this->belongsTo(User::class,'receiver_id');
    }

    public function payment_method() {
        return $this->belongsTo(PaymentMethod::class,'payment_ms','slug');
    }
}
