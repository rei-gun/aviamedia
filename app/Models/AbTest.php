<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AbTest extends Model
{
    use HasFactory;

    protected $table = 'ab_tests';

    protected $fillable = [
        'name',
        'is_active',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
    
    public function variants() {
        return $this->hasMany(AbTestVariant::class);
    }

    // ensure the name attribute is always stored capitalized
    public function setNameAttribute($value) {
        $this->attributes['name'] = ucfirst($value);
    }

    /* getActive() will get A/B tests that are active and if there are not-null started_at or ended_at values
     * return rows that are within the 2 values or if only started_at is filled, values that are after started_at
     */
    public static function getActive() {
        $activeTests = static::where('is_active', true)
                ->where(function ($query) {
                    $now = Carbon::now();
                    $query->whereNull('started_at') // started_at is null (open-ended start)
                          ->orWhere('started_at', '<=', $now); // or started_at is in the past
                })
                ->where(function ($query) {
                    $now = Carbon::now();
                    $query->whereNull('ended_at') // ended_at is null (open-ended end)
                          ->orWhere('ended_at', '>', $now); // or ended_at is in the future
                })
                ->get();
        return $activeTests;
    }
}
