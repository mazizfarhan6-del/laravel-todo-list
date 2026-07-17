<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Tambahkan ini

class Todo extends Model
{
    use HasFactory, SoftDeletes; // Aktifkan fitur SoftDeletes di sini

    protected $fillable = ['category_id', 'title', 'description', 'status', 'priority', 'due_date'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}