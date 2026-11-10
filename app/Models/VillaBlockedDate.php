<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillaBlockedDate extends Model
{
    protected $fillable = ['villa_id', 'date', 'note'];

    protected $casts = ['date' => 'date'];

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
