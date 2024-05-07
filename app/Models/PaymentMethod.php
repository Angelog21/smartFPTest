<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;


class PaymentMethod extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'name',
        'slug'
    ];

    protected $keyType = 'string';

    public $timestamps = false;

    public function payments() {
        return $this->hasMany(Payment::class,'payment_ms','slug');
    }
}
