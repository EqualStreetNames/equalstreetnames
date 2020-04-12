<?php

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

$json = json_decode(file_get_contents('data/overpass/relation/full.json'), true);

$nodes = extractNodes($json['elements']);
$ways = extractWays($json['elements']);
$relations = extractRelations($json['elements']);

$waysInRelation = array_keys($ways);

$streetsGender = readStreetsGender();

$instances = include 'scripts/instances.php';

$geojson = [
    'type'     => 'FeatureCollection',
    'features' => [],
];

foreach ($relations as $r) {
    $properties = extractTags('relation', $r, $streetsGender);

    $streets = array_filter(
        $r['members'],
        function ($member) {
            return $member['role'] === 'street' || $member['role'] === 'outer';
        }
    );

    $linestrings = [];
    foreach ($streets as $street) {
        if (isset($ways[$street['ref']])) {
            $linestrings[] = appendCoordinates($nodes, $ways[$street['ref']]);
        } else {
            printf('Can\'t find way(%d) from relation(%d).%s', $street['ref'], $r['id'], PHP_EOL);
        }
    }

    if (count($linestrings) === 0) {
        printf('No geometry for relation(%d).%s', $r['id'], PHP_EOL);
    }

    $geojson['features'][] = [
        'type'       => 'Feature',
        'id'         => $r['id'],
        'properties' => $properties,
        'geometry'   => makeGeometry($linestrings),
    ];
}

file_put_contents('static/relations.geojson', json_encode($geojson));

unset($json, $geojson);

$json = json_decode(file_get_contents('data/overpass/way/full.json'), true);

$nodes = extractNodes($json['elements']);
$ways = extractWays($json['elements']);

$geojson = [
    'type'     => 'FeatureCollection',
    'features' => [],
];

foreach ($ways as $w) {
    if (!in_array($w['id'], $waysInRelation)) {
        $properties = extractTags('way', $w, $streetsGender);

        $linestring = appendCoordinates($nodes, $w);

        $geojson['features'][] = [
            'type'       => 'Feature',
            'id'         => $w['id'],
            'properties' => $properties,
            'geometry'   => makeGeometry(is_null($linestring) ? null : [$linestring]),
        ];
    }
}

file_put_contents('static/ways.geojson', json_encode($geojson));

exit(0);

function extractTags(string $type, array $object, array $gender): array
{
    $properties = [
        'name'      => $object['tags']['name'] ?? null,
        'name:fr'   => $object['tags']['name:fr'] ?? null,
        'name:nl'   => $object['tags']['name:nl'] ?? null,
        'wikidata'  => $object['tags']['wikidata'] ?? null,
        'gender'    => null,
        'details'   => null,
    ];

    if (isset($object['tags']['name:etymology:wikidata'])) {
        $etymology = explode(';', $object['tags']['name:etymology:wikidata']);
        $etymology = array_map('trim', $etymology);

        if (count($etymology) === 1) {
            $etymology = current($etymology);
        }

        if (is_array($etymology)) {
            printf('Multiple instances for %s(%d).%s', $type, $object['id'], PHP_EOL);

            $details = [];
            foreach ($etymology as $e) {
                $details[] = extractWikidata($e);
            }

            $properties = array_merge(
                $properties,
                [
                    'details' => $details,
                ]
            );

            $person = array_unique(array_column($properties['details'], 'person'));
            $gender = array_unique(array_column($properties['details'], 'gender'));

            $properties['gender'] = (count($person) === 1 && current($person) === true) ? (count($gender) === 1 ? current($gender) : '+') : null;
        } else {
            $properties = array_merge(
                $properties,
                [
                    'details' => extractWikidata($etymology),
                ]
            );

            $properties['gender'] = $properties['details']['person'] === true ? $properties['details']['gender'] : null;
        }
    } else {
        $properties['gender'] = getGender(
            $gender,
            $object['tags']['name:fr'] ?? $object['tags']['name'],
            $object['tags']['name:nl'] ?? $object['tags']['name']
        );
    }

    return $properties;
}

function extractNodes(array $elements): array
{
    $filter = array_filter(
        $elements,
        function ($element) {
            return $element['type'] === 'node';
        }
    );

    $nodes = [];

    foreach ($filter as $f) {
        $nodes[$f['id']] = $f;
    }

    return $nodes;
}

function extractWays(array $elements): array
{
    $filter = array_filter(
        $elements,
        function ($element) {
            return $element['type'] === 'way';
        }
    );

    $ways = [];

    foreach ($filter as $f) {
        $ways[$f['id']] = $f;
    }

    return $ways;
}

function extractRelations(array $elements): array
{
    $filter = array_filter(
        $elements,
        function ($element) {
            return $element['type'] === 'relation';
        }
    );

    $relations = [];

    foreach ($filter as $f) {
        $relations[$f['id']] = $f;
    }

    return $relations;
}

function appendCoordinates(array $nodes, array $way): ?array
{
    $linestring = [];

    foreach ($way['nodes'] as $id) {
        $node = $nodes[$id] ?? null;

        if (is_null($node)) {
            printf('Can\'t find node(%d) in way(%d).%s', $id, $way['id'], PHP_EOL);
        } else {
            $linestring[] = [
                $node['lon'],
                $node['lat'],
            ];
        }
    }

    if (count($linestring) === 0) {
        printf('No geometry for way(%d).%s', $way['id'], PHP_EOL);
    }

    return count($linestring) === 0 ? null : $linestring;
}

function makeGeometry(array $linestrings): ?array
{
    if (count($linestrings) === 0) {
        return null;
    } elseif (count($linestrings) > 1) {
        return [
            'type'        => 'MultiLineString',
            'coordinates' => $linestrings,
        ];
    } else {
        return [
            'type'        => 'LineString',
            'coordinates' => $linestrings[0],
        ];
    }
}

function extractWikidata(string $identifier): ?array
{
    global $instances;

    $path = sprintf('data/wikidata/%s.json', $identifier);

    if (!file_exists($path)) {
        printf('Missing file for %s.%s', $identifier, PHP_EOL);

        return null;
    }

    $json = json_decode(file_get_contents($path), true);

    $entity = $json['entities'][$identifier] ?? null;

    if (is_null($entity)) {
        printf('Entity %s missing in "%s".%s', $identifier, basename($path), PHP_EOL);

        return null;
    }

    $instance = $entity['claims']['P31'] ?? $entity['claims']['P279'] ?? null;

    $person = false;
    if (is_null($instance)) {
        printf('No instance or subclass for %s.%s', $identifier, PHP_EOL);
    } else {
        foreach ($instance as $p) {
            $value = $p['mainsnak']['datavalue']['value']['id'];
            if (isset($instances[$value])) {
                if ($instances[$value] === true) {
                    $person = true;
                    break;
                }
            } else {
                printf('New instance %s for %s.%s', $value, $identifier, PHP_EOL);
            }
        }
    }

    $labels = array_filter(
        $entity['labels'],
        function ($language) {
            return in_array($language, ['de', 'en', 'fr', 'nl']);
        },
        ARRAY_FILTER_USE_KEY
    );

    $descriptions = array_filter(
        $entity['descriptions'],
        function ($language) {
            return in_array($language, ['de', 'en', 'fr', 'nl']);
        },
        ARRAY_FILTER_USE_KEY
    );

    $sitelinks = array_filter(
        $entity['sitelinks'],
        function ($language) {
            return in_array($language, ['dewiki', 'enwiki', 'frwiki', 'nlwiki']);
        },
        ARRAY_FILTER_USE_KEY
    );

    $genderId = $entity['claims']['P21'][0]['mainsnak']['datavalue']['value']['id'] ?? null;

    // $image = $entity['claims']['P18'][0]['mainsnak']['datavalue']['value'] ?? null;

    $dateOfBirth = $entity['claims']['P569'][0]['mainsnak']['datavalue']['value']['time'] ?? null;
    $dateOfDeath = $entity['claims']['P570'][0]['mainsnak']['datavalue']['value']['time'] ?? null;

    return [
        'wikidata'     => $identifier,
        'person'       => $person,
        'labels'       => $labels,
        'descriptions' => $descriptions,
        'gender'       => is_null($genderId) ? null : extractGender($genderId),
        'birth'        => is_null($dateOfBirth) ? null : intval(substr($dateOfBirth, 1, 4)),
        'death'        => is_null($dateOfDeath) ? null : intval(substr($dateOfDeath, 1, 4)),
        // 'image'        => is_null($image) ? null : sprintf('https://commons.wikimedia.org/wiki/File:%s', $image),
        'sitelinks'    => $sitelinks,
    ];
}

function extractGender(string $identifier): ?string
{
    switch ($identifier) {
        case 'Q6581097': // male
        case 'Q1052281': // male (cis)
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

        default:
            printf('Undefined gender %s.%s', $identifier, PHP_EOL);

            return null;
    }
}

function readStreetsGender(): array
{
    $streets = [];

    if (($handle = fopen('data/event-2020-02-17/gender.csv', 'r')) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            $streets[] = $data;
        }
        fclose($handle);
    }

    return $streets;
}

function getGender(array $streets, string $nameFr, string $nameNl): ?string
{
    $filter = array_filter(
        $streets,
        function ($street) use ($nameFr, $nameNl) {
            return $street[0] === $nameFr || $street[1] === $nameNl;
        }
    );

    if (count($filter) === 0) {
        return null;
    } elseif (count($filter) === 1) {
        $street = current($filter);

        return $street[2];
    } else {
        // printf('Multiple records for street "%s - %s".%s', $nameFr, $nameNl, PHP_EOL);

        $gender = [];
        foreach ($filter as $street) {
            if (!in_array($street[2], $gender)) {
                $gender[] = $street[2];
            }
        }

        if (count($gender) === 0) {
            return null;
        } elseif (count($gender) === 1) {
            return current($gender);
        } else {
            printf('Ambiguous gender for street "%s - %s".%s', $nameFr, $nameNl, PHP_EOL);

            return null;
        }
    }
}
