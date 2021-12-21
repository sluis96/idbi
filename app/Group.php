<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre', 'descripcion',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
