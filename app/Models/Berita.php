<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'beritas';

    protected $fillable = ['title', 'content', 'author_id'];

    /**
     * Relasi: Sebuah Berita ditulis oleh satu User (author).
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
