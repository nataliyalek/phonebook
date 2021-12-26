<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'note',
        'created_at',
        'updated_at',
    ];

    public function phones()
    {
        return $this->hasMany(Contact::class);
    }
}
