<?php

declare(strict_types=1);

/**
 * Extract nodes|ways|relations from Overpass result.
 *
 * @param string $type     OpenStreetMap element type (`node|way|relation`).
 * @param array  $elements Overpass result elements.
 *
 * @return array Associative array of nodes|ways|relations
 *               with node|way|relation id as key.
 */
function extractElements(string $type, array $elements): array
{
    $filter = array_filter(
        $elements,
        function ($element) use ($type) {
            return $element['type'] === $type;
        }
    );

    $result = [];

    foreach ($filter as $f) {
        $result[$f['id']] = $f;
    }

    return $result;
}

/**
 * Extract useful tags from Overpass result.
 *
 * @param string   $type      OpenStreetMap element type (`node|way|relation`).
 * @param array    $element   Overpass result element.
 * @param string[] $languages Required languages.
 * @param array    $instances Array that defines what instances are
 *                            considered as a person.
 * @param array    $gender    Manually assigned street gender
 *                            (via configuration).
 * @param array    $manual    Manually assigned street gender
 *                            (via CSV file - only available for Brussels).
 *
 * @return array
 */
function extractTags(
    string $type,
    array $element,
    array $languages,
    array $instances,
    array $gender,
    array $manual
): array {
    $properties = [
        'name'      => $element['tags']['name'] ?? null,
        'wikidata'  => $element['tags']['wikidata'] ?? null,
        'gender'    => null,
        'details'   => null,
    ];

    if (isset($element['tags']['name:etymology:wikidata'])) {
        // Extract gender and person information from Wikidata
        $etymology = explode(';', $element['tags']['name:etymology:wikidata']);
        $etymology = array_map('trim', $etymology);

        if (count($etymology) === 1) {
            $etymology = current($etymology);
        }

        if (is_array($etymology)) {
            printf(
                'Multiple instances for %s(%d).%s',
                $type,
                $element['id'],
                PHP_EOL
            );

            $details = [];
            foreach ($etymology as $e) {
                $details[] = extractWikidata($e, $languages, $instances);
            }

            $properties = array_merge(
                $properties,
                [
                    'details' => $details,
                ]
            );

            $_person = array_unique(array_column($properties['details'], 'person'));
            $_gender = array_unique(array_column($properties['details'], 'gender'));

            $properties['gender'] = (count($_person) === 1 && current($_person) === true) ? (count($_gender) === 1 ? current($_gender) : '+') : null;
        } else {
            $properties = array_merge(
                $properties,
                [
                    'details' => extractWikidata($etymology, $languages, $instances),
                ]
            );

            if (!is_null($properties['details'])) {
                $properties['gender'] = $properties['details']['person'] === true ?
                    $properties['details']['gender'] : null;
            }
        }
    } elseif ($type === 'relation' && isset($gender['relation'], $gender['relation'][(string) $element['id']])) {
        $properties['gender'] = $gender['relation'][(string) $element['id']];
    } elseif ($type === 'way' && isset($gender['way'], $gender['way'][(string) $element['id']])) {
        $properties['gender'] = $gender['way'][(string) $element['id']];
    } elseif (count($manual) > 0) {
        // Extract gender from manual work (only available for Brussels)
        $properties['gender'] = getGender(
            $manual,
            $element['tags']['name:fr'] ?? $element['tags']['name'],
            $element['tags']['name:nl'] ?? $element['tags']['name']
        );
    }

    return $properties;
}
