<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory,SoftDeletes,Prunable;


    protected $fillable = [
        'name',
        'type',
        'ancestors',
        'user_id',
        'parent_id',
    ];

    public function parent(){
        return $this->belongsTo(Folder::class, 'parent_id');
    }


    public function subFolders(){
        return $this->hasMany(Folder::class,'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function getAncestorsArrayAttribute()
    {
        return explode(',',$this->ancestors);
    }


}
