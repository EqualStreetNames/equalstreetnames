<?php

namespace App\Model\Config;

class ExcludeRelationWay
{
    /** @var int[] */
    public array $relation;
    /** @var int[] */
    public array $way;

    public function __construct(array $relation, array $way)
    {
        $this->relation = $relation;
        $this->way = $way;
    }
}
