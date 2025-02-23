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

    public function children()
    {
        return $this->where('ancestors','like','%,'.$this->folder->id.',%');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function usersPermissions()
    {
        return $this->belongsToMany(User::class,'permissions','folder_id','user_id')
            ->withPivot('permission');
    }

    public function getAncestorsArrayAttribute()
    {
        return explode(',',$this->ancestors);
    }




}
