<?php

namespace App\Interfaces;

interface Searchable
{
    public function getSearchableFields(): array;
}