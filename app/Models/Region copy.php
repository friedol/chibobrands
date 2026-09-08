<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['region_name', 'region_code'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
