<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeGetParent($query)
    {
        return $query->whereNull('parent_id');
    }
}
