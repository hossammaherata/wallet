<?php

namespace App\Traits;

trait SearchTrait
{
    /**
     * Scope a query to filter results by a keyword, including related models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    
    public function scopeOfKeyword($query, $keyword)
    {
        $columns = implode(',', $this->getSearchableFields());
        
        if (empty($keyword) || empty($columns)) {
            return $query;
        }

        $query = $query->whereRaw("LOWER(CONCAT_WS(' ', {$columns})) LIKE ?", ['%' . strtolower($keyword) . '%']);
    
        if (!empty($this->searchableRelations)) {
            foreach ((array) $this->searchableRelations as $relation) {
                if (method_exists($this, $relation)) {
                    $relatedModel = $this->{$relation}()->getModel();
                    $relationSearchable = $relatedModel->getSearchableFields();
                        // dd($relationSearchable);
                    if (!empty($relationSearchable)) {
                        $relationColumns = implode(',', $relationSearchable);
                        $query = $query->orWhereHas($relation, function ($query) use ($relationColumns, $keyword) {
                            $query->whereRaw("LOWER(CONCAT_WS(' ', {$relationColumns})) LIKE ?", ['%' . strtolower($keyword) . '%']);
                        });
                    }
                }
            }
        }
           
        return $query;
    }

    /**
     * Scope a query to filter results by related models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfHas($query, $relations)
    {
        foreach ((array) $relations as $relation) {
            $query->has($relation);
        }
        return $query;
    }
}
