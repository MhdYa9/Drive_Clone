<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'type',
        'parent_id',
    ];

    public function parent(){
        return $this->belongsTo(Folder::class);
    }

    public function children(){
        return $this->hasMany(Folder::class);
    }

    public function scopeSubFolders(){

    }

}
