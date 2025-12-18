<?php

namespace App\Models;

use App\Interfaces\Searchable;
use App\Traits\SearchTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model implements Searchable
{
    use HasFactory, SearchTrait, SoftDeletes;

    protected $fillable = [
        'shamel_id',
        'name_ar',
        'name_heb',
    ];

    protected $searchable = ['shamel_id', 'name_ar', 'name_heb'];
    protected $searchableRelations = [];

    public function getSearchableFields(): array
    {
        return $this->searchable ?? [];
    }
}
