<?php

namespace App\Model;

use ArrayObject;

class ExcludeRelationWay {
  /** @var int[] */
  public array $relation;
  /** @var int[] */
  public array $way;
}

class GenderRelationWay {
  /** @var array<string,string> */
  public array $relation;
  /** @var array<string,string> */
  public array $way;
}

class Config {
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
    $this->exclude = $config['exclude'];
    $this->gender = $config['gender'];
    $this->instances = $config['instances'];
  }
}
