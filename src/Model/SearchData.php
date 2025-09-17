<?php

namespace App\Model;

use App\Entity\Brand;
use App\Entity\Category;

class SearchData
{
    public string $q = '';
    public ?Category $c = null;
    /**
     * @var Brand[]
     */
    public array $b = [];
}
