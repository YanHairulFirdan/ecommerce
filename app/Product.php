<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'name',
        'category_id',
        'description',
        'image',
        'price',
        'weight',
        'status'
    ];

    public function getStatusLabelAttribute()
    {
        if (!$this->status) {
            return '<span class="badge badge-secodary">Draft</span>';
        }
        return '<span class="badge badge-success">Aktif</span>';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
