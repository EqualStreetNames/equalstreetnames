<?php

declare(strict_types=1);

/**
 * Remove accents from string.
 *
 * @param string $str     String to process.
 * @param string $charset Character set (URT-8 by default).
 *
 * @return string
 */
function removeAccents(string $str, string $charset = 'utf-8'): string
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    $str = preg_replace('#&[^;]+;#', '', $str);

    return $str;
}

/**
 * Check if the streetname has already be "processed".
 *
 * @param string $name        Streetname.
 * @param string|null $gender Gender (F, M, FX, MX, X, ...).
 * @param array  $streets Array of already processed streets.
 *
 * @return array|false
 */
function already(string $name, ?string $gender, array $streets)
{
    $filter = array_filter(
        $streets,
        function (array $street) use ($name, $gender): bool {
            return removeAccents($street['name']) == removeAccents($name)
                && $street['gender'] == $gender;
        }
    );

    return count($filter) > 0 ? current($filter) : false;
}

/**
 * Extract useful data for CSV report.
 *
 * @param string $type    OpenStreetMap element type (`node|way|relation`).
 * @param object $feature GeoJSON Feature.
 * @param array  $streets Array of already processed streets.
 *
 * @return array|false
 */
function extractData(string $type, object $feature, array $streets)
{
    if (!is_null($feature->properties->details) && is_array($feature->properties->details)) {
        $wikidata = implode(';', array_column($feature->properties->details, 'wikidata'));
    } else {
        $wikidata = $feature->properties->details->wikidata ?? null;
    }

    $data = [
        'name'     => $feature->properties->name,
        'source'   => $feature->properties->source ?? null,
        'gender'   => $feature->properties->gender ?? null,
        'wikidata' => $wikidata,
        'type'     => $type,
    ];

    if (is_null($data['name'])) {
        printf('Streetname should not be NULL: %s(%s).%s', $type, $feature->id, PHP_EOL);

        return false;
    }

    if (($street = already($data['name'], $data['gender'], $streets)) === false) {
        return $data;
    } elseif (!is_null($street['wikidata']) && !is_null($data['wikidata']) && $street['wikidata'] !== $data['wikidata']) {
        printf('Wikidata mismatch: %s : %s <> %s%s', $data['name'], $data['wikidata'], $street['wikidata'], PHP_EOL);

        return false;
    }

    return false;
}
