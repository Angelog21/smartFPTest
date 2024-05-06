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

    public $timestamps = false;
}
