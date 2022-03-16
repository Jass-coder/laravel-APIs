<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'post_time',
        'post_date',
    ];

    public function user(){
        return $this->belongsTo(User::class,'added_by');
    }

}
