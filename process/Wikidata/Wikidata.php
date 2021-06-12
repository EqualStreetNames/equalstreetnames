<?php

namespace App\Wikidata;

use App\Exception\FileException;
use App\Exception\WikidataException;
use App\Model\Wikidata\Entity;

class Wikidata
{
    /**
     * @param string $path
     *
     * @return Entity
     *
     * @throws FileException
     * @throws WikidataException
     */
    public static function read(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new FileException(sprintf('<warning>File "%s" doesn\'t exist or is not readable. You maybe need to run "wikidata" command first.</warning>', $path));
        }

        $content = file_get_contents($path);
        $json = $content !== false ? json_decode($content) : null;
        if (is_null($json)) {
            throw new WikidataException(sprintf('Can\'t read "%s".', $path));
        }

        return current($json->entities);
    }

    /**
     * @param Entity $entity
     * @param string[] $languages
     *
     * @return array<string,string>
     */
    public static function extractLabels($entity, array $languages): array
    {
        $labels = [];

        foreach ($languages as $language) {
            if (isset($entity->labels->{$language})) { // @phpstan-ignore-line
                $labels[$language] = $entity->labels->{$language}; // @phpstan-ignore-line
            }
        }

        return $labels;
    }

    /**
     * @param Entity $entity
     * @param string[] $languages
     *
     * @return array<string,string>
     */
    public static function extractDescriptions($entity, array $languages): array
    {
        $descriptions = [];

        foreach ($languages as $language) {
            if (isset($entity->descriptions->{$language})) { // @phpstan-ignore-line
                $descriptions[$language] = $entity->descriptions->{$language}; // @phpstan-ignore-line
            }
        }

        return $descriptions;
    }

    /**
     * @param Entity $entity
     * @param string[] $languages
     *
     * @return array<string,string>
     */
    public static function extractSitelinks($entity, array $languages): array
    {
        $sitelinks = [];

        foreach ($languages as $language) {
            if (isset($entity->sitelinks->{$language . 'wiki'})) { // @phpstan-ignore-line
                $sitelinks[$language . 'wiki'] = $entity->sitelinks->{$language . 'wiki'}; // @phpstan-ignore-line
            }
        }

        return $sitelinks;
    }

    /**
     * @param Entity $entity
     * @param string[] $languages
     *
     * @return null|array<string,string>
     */
    public static function extractNicknames($entity, array $languages): ?array
    {
        $nicknames = null;

        $claims = $entity->claims->P1449 ?? [];

        foreach ($claims as $value) {
            $language = $value->mainsnak->datavalue->value->language; // @phpstan-ignore-line

            if (in_array($language, $languages, true)) {
                $nicknames[$language] = $value->mainsnak->datavalue->value; // @phpstan-ignore-line
            }
        }

        return $nicknames;
    }

    /**
     * @param Entity $entity
     *
     * @return null|string[]
     */
    public static function extractNamedAfter($entity): ?array
    {
        /** @var string[] */
        $identifiers = [];

        $claims = $entity->claims->P138 ?? [];

        foreach ($claims as $value) {
            $endTime = $value->qualifiers->P582[0]->datavalue->value->time ?? null; // @phpstan-ignore-line
            if (!is_null($endTime) && $endTime < date('c')) {
                continue;
            }

            /** @var string */
            $id = $value->mainsnak->datavalue->value->id; // @phpstan-ignore-line

            $identifiers[] = $id;
        }

        return count($identifiers) === 0 ? null : $identifiers;
    }

    /**
     * @param Entity $entity
     */
    public static function extractDateOfBirth($entity): ?string
    {
        return isset($entity->claims->P569) ? $entity->claims->P569[0]->mainsnak->datavalue->value->time ?? null : null; // @phpstan-ignore-line
    }

    /**
     * @param Entity $entity
     */
    public static function extractDateOfDeath($entity): ?string
    {
        return isset($entity->claims->P570) ? $entity->claims->P570[0]->mainsnak->datavalue->value->time ?? null : null; // @phpstan-ignore-line
    }

    /**
     * @param Entity $entity
     */
    public static function extractImage($entity): ?string
    {
        return isset($entity->claims->P18) ? $entity->claims->P18[0]->mainsnak->datavalue->value ?? null : null; // @phpstan-ignore-line
    }

    /**
     * @param Entity $entity
     */
    public static function extractGender($entity): ?string
    {
        $identifier = isset($entity->claims->P21) ? $entity->claims->P21[0]->mainsnak->datavalue->value->id ?? null : null; // @phpstan-ignore-line

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

    /**
     * @param Entity $entity
     *
     * @return string[]
     */
    private static function extractInstances($entity): ?array
    {
        $property = $entity->claims->P31 ?? $entity->claims->P279 ?? null;

        if (is_null($property)) {
            return null;
        }

        return array_map(function ($p) {
            return $p->mainsnak->datavalue->value->id; // @phpstan-ignore-line
        }, $property);
    }

    /**
     * @param Entity $entity
     * @param array<string,bool> $instances
     */
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
