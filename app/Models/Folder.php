<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Type\Integer;

class Folder extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'type',
        'parent_id',
    ];

    public function parent(){
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(){
        return $this->hasMany(Folder::class,'parent_id');
    }


}
