<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /**
     * Relationships
     */

    public function commerces()
    {
        return $this->hasMany(Commerce::class);
    }
}
