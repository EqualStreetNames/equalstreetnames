<?php

namespace App\Wikidata;

class Wikidata
{
  public static function extractLabels($entity, array $languages): array
  {
    $labels = [];

    foreach ($languages as $language) {
      if (isset($entity->labels->{$language})) {
        $labels[$language] = $entity->labels->{$language};
      }
    }

    return $labels;
  }

  public static function extractDescriptions($entity, array $languages): array
  {
    $descriptions = [];

    foreach ($languages as $language) {
      if (isset($entity->descriptions->{$language})) {
        $descriptions[$language] = $entity->descriptions->{$language};
      }
    }

    return $descriptions;
  }

  public static function extractSitelinks($entity, array $languages): array
  {
    $sitelinks = [];

    foreach ($languages as $language) {
      if (isset($entity->sitelinks->{$language.'wiki'})) {
        $sitelinks[$language.'wiki'] = $entity->sitelinks->{$language.'wiki'};
      }
    }

    return $sitelinks;
  }

  public static function extractNicknames($entity, array $languages): ?array
  {
    $nicknames = null;

    $claims = $entity->claims->P1449 ?? [];

    foreach ($claims as $value) {
      $language = $value->mainsnak->datavalue->value->language;

      if (in_array($language, $languages, true)) {
        $nicknames[$language] = $value->mainsnak->datavalue->value;
      }
    }

    return $nicknames;
  }

  public static function extractDateOfBirth($entity): ?string
  {
    return $entity->claims->P569[0]->mainsnak->datavalue->value->time ?? null;
  }

  public static function extractDateOfDeath($entity): ?string
  {
    return $entity->claims->P570[0]->mainsnak->datavalue->value->time ?? null;
  }

  public static function extractImage($entity): ?string
  {
    return $entity->claims->P18[0]->mainsnak->datavalue->value ?? null;
  }

  public static function extractGender($entity): ?string
  {
    $identifier = $entity->claims->P21[0]->mainsnak->datavalue->value->id ?? null;

    switch ($identifier) {
      case 'Q6581097': // male
      case 'Q15145778': // male (cis)
        return 'M';

      case 'Q6581072': // female
      case 'Q15145779': // female (cis)
        return 'F';

      case 'Q1052281': // female (trans)
        return 'FX';

      case 'Q2449503': // male (trans)
        return 'MX';

      case 'Q1097630': // intersex
        return 'X';

      case 'Q48270': // non-binary
        return 'NB';

      default:
        return null;
    }
  }

  private static function extractInstances($entity): ?array
  {
    $property = $entity->claims->P31 ?? $entity->claims->P279 ?? null;

    if (is_null($property)) {
      return null;
    }

    return array_map(function ($p) { return $p->mainsnak->datavalue->value->id; }, $property);
  }

  public static function isPerson($entity, array $instances): ?bool
  {
    $identifiers = self::extractInstances($entity);

    if (is_null($identifiers)) {
      return null;
    }

    $person = false;
    foreach ($identifiers as $id) {
      if (isset($instances[$id]) && $instances[$id] === true) {
        $person = true;
        break;
      }
    }

    return $person;
  }
}
