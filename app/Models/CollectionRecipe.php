<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'recipe_id',
    ];
}
