<?php

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

$json = json_decode(file_get_contents('data/overpass/associatedStreet/full.json'), true);

$nodes = extractNodes($json['elements']);
$ways = extractWays($json['elements']);
$relations = extractRelations($json['elements']);

$waysInRelation = array_keys($ways);

$geojson = [
    'type'     => 'FeatureCollection',
    'features' => [],
];

foreach ($relations as $r) {
    $properties = [
        'name'      => $r['tags']['name'] ?? null,
        'name:fr'   => $r['tags']['name:fr'] ?? null,
        'name:nl'   => $r['tags']['name:nl'] ?? null,
        'wikidata'  => $r['tags']['wikidata'] ?? null,
        'etymology' => $r['tags']['name:etymology:wikidata'] ?? null,
    ];

    $streets = array_filter(
        $r['members'],
        function ($member) {
            return $member['role'] === 'street';
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

file_put_contents('data/relations.geojson', json_encode($geojson));

unset($json, $geojson);

$json = json_decode(file_get_contents('data/overpass/highway/full.json'), true);

$nodes = extractNodes($json['elements']);
$ways = extractWays($json['elements']);

$geojson = [
    'type'     => 'FeatureCollection',
    'features' => [],
];

foreach ($ways as $w) {
    if (!in_array($w['id'], $waysInRelation)) {
        $properties = [
            'name'      => $w['tags']['name'] ?? null,
            'name:fr'   => $w['tags']['name:fr'] ?? null,
            'name:nl'   => $w['tags']['name:nl'] ?? null,
            'wikidata'  => $w['tags']['wikidata'] ?? null,
            'etymology' => $w['tags']['name:etymology:wikidata'] ?? null,
        ];

        $linestring = appendCoordinates($nodes, $w);

        $geojson['features'][] = [
            'type'       => 'Feature',
            'id'         => $w['id'],
            'properties' => $properties,
            'geometry'   => makeGeometry(is_null($linestrings) ? null : [$linestring]),
        ];
    }
}

file_put_contents('data/ways.geojson', json_encode($geojson));

exit(0);

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
