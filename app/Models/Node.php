<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'type',
        'parent_id',
    ];

    public function parent(){
        return $this->belongsTo(Node::class);
    }

    public function children(){
        return $this->hasMany(Node::class);
    }

}
