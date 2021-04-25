<?php

namespace App\Model\Wikidata;

class LanguageValue {
  public string $language;
  public string $value;
}

class Claims {
  /** @var null|array<object> image */
  public ?array $P18;
  /** @var null|array<object> sex or gender */
  public ?array $P21;
  /** @var null|array<object> instance of */
  public ?array $P31;
  /** @var null|array<object> subclass of */
  public ?array $P279;
  /** @var null|array<object> date of birth */
  public ?array $P569;
  /** @var null|array<object> date of death */
  public ?array $P570;
  /** @var null|array<object> nickname */
  public ?array $P1449;
}

class Entity {
  public string $id;

  public object $labels;

  public object $descriptions;

  public object $sitelinks;

  /** @var Claims */
  public object $claims;
}
