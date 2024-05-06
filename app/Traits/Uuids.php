<?php

namespace App\Traits;

use Webpatser\Uuid\Uuid;

trait Uuids
{
	protected static function boot()
    {
		parent::boot();
		
		//generating uuid for each insert to table
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }
}