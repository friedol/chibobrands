<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'button_text',
        'button_url',
        'button_color',
        'sort_order',
        'is_active',
        'page_type',
        'is_ad',
        'ad_type',
        'ad_position',
        'ad_duration',
        'ad_closable',
        'ad_target_audience',
        'ad_start_date',
        'ad_end_date',
        'ad_click_count',
        'ad_impression_count',
        'ad_budget',
        'ad_cost_per_click',
        'ad_cost_per_impression'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'is_ad' => 'boolean',
        'ad_closable' => 'boolean',
        'ad_duration' => 'integer',
        'ad_click_count' => 'integer',
        'ad_impression_count' => 'integer',
        'ad_budget' => 'decimal:2',
        'ad_cost_per_click' => 'decimal:2',
        'ad_cost_per_impression' => 'decimal:2',
        'ad_start_date' => 'date',
        'ad_end_date' => 'date'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function scopeForPage($query, $pageType)
    {
        return $query->where(function ($q) use ($pageType) {
            $q->where('page_type', 'all')
              ->orWhere('page_type', $pageType);
        });
    }

    public function scopeForHomepage($query)
    {
        return $query->forPage('homepage');
    }

    public function scopeForServices($query)
    {
        return $query->forPage('services');
    }

    public function scopeForProducts($query)
    {
        return $query->forPage('products');
    }

    public function scopeForWholesaleServices($query)
    {
        return $query->forPage('wholesale_services');
    }

    public function scopeAds($query)
    {
        return $query->where('is_ad', true);
    }

    public function scopeSlides($query)
    {
        return $query->where('is_ad', false);
    }

    public function scopeActiveAds($query)
    {
        return $query->ads()->active()->where(function ($q) {
            $q->whereNull('ad_start_date')
              ->orWhere('ad_start_date', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ad_end_date')
              ->orWhere('ad_end_date', '>=', now());
        });
    }

    public function scopeForAdType($query, $adType)
    {
        return $query->where('ad_type', $adType);
    }

    public function scopeForAdPosition($query, $position)
    {
        return $query->where('ad_position', $position);
    }

    public function scopeForTargetAudience($query, $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('ad_target_audience', 'all')
              ->orWhere('ad_target_audience', $audience);
        });
    }

    public function incrementClickCount()
    {
        $this->increment('ad_click_count');
    }

    public function incrementImpressionCount()
    {
        $this->increment('ad_impression_count');
    }

    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->ad_start_date && $this->ad_start_date > now()) {
            return false;
        }

        if ($this->ad_end_date && $this->ad_end_date < now()) {
            return false;
        }

        return true;
    }

    public function isVideo()
    {
        if (!$this->image_path) return false;
        $extension = strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION));
        return in_array($extension, ['mp4', 'webm', 'ogg', 'mov']);
    }
}
