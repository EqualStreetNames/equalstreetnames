<?php

namespace App\Model\Config;

class Config
{
    public int $relationId;

    /** @var string[] */
    public array $languages;

    public ExcludeRelationWay $exclude;

    public GenderRelationWay $gender;

    /** @var array<string,bool> */
    public array $instances;

    public function __construct(array $config)
    {
        $this->relationId = $config['relationId'];
        $this->languages = $config['languages'];
        $this->exclude = new ExcludeRelationWay($config['exclude']['relation'] ?? [], $config['exclude']['way'] ?? []);
        $this->gender = new GenderRelationWay($config['gender']['relation'] ?? [], $config['gender']['way'] ?? []);
        $this->instances = $config['instances'];
    }
}
