<?php

namespace App\Models;

use App\Http\Controllers\FolderController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory,SoftDeletes,Prunable;

    protected $fillable = [
        'name',
        'path',
        'folder_id'
    ];

    public function folder(){
        return $this->belongsTo(Folder::class);
    }

    public function getFullPathAttribute(){

    }



}
