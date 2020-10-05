<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPrice extends Model
{
    use SoftDeletes;

    /**
     * Relationships
     */

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}