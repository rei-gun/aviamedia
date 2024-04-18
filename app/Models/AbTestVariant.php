<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbTestVariant extends Model
{
    use HasFactory;

    protected $table = 'ab_test_variants';

    protected $fillable = 
    [
        'ab_test_id',
        'name',
        'targeting_ratio',
    ];

    public function abTest() 
    {
        return $this->belongsTo(AbTest::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
