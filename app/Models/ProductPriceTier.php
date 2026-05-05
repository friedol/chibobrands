<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceTier extends Model
{
    protected $fillable = ['product_id','min_quantity','max_quantity','price_per_unit','is_wholesale'];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
