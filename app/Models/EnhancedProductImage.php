<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnhancedProductImage extends Model
{
    use HasFactory;

    protected $table = 'enhanced_product_images';

    protected $fillable = [
        'product_id', 'color', 'image_path'
    ];

    public function product()
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrlAttribute()
    {
        $path = pathinfo($this->image_path);
        $thumbnailPath = $path['dirname'] . '/thumbnails/' . $path['filename'] . '_thumb.' . $path['extension'];
        return asset('storage/' . $thumbnailPath);
    }
}
