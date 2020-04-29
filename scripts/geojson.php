<?php

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';
require 'library/geojson/openstreetmap.php';
require 'library/geojson/wikidata.php';
require 'library/geojson/geometry.php';
require 'library/geojson/gender.php';

$options = getopt('c:', ['city:']);

$city = $options['c'] ?? $options['city'];

$config = include sprintf('cities/%s/config.php', $city);

// Need to be removed when each Brussels streets
// have its `name:etymology:wikidata` tag
$manualStreetsGender = [];
if ($city === 'brussels') {
    $handle = fopen('cities/brussels/event-2020-02-17/gender.csv', 'r');
    if ($handle !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            $manualStreetsGender[] = $data;
        }
        fclose($handle);
    }
}

// Process relations
$json = json_decode(file_get_contents('data/overpass/relation/full.json'), true);

$nodes = extractElements('node', $json['elements']);
$ways = extractElements('way', $json['elements']);
$relations = extractElements('relation', $json['elements']);

$waysInRelation = array_keys($ways);

$geojson = [
    'type'     => 'FeatureCollection',
    'features' => [],
];

foreach ($relations as $r) {
    if (isset($config['exclude'], $config['exclude']['relation']) && is_array($config['exclude']['relation']) && in_array($r['id'], $config['exclude']['relation'], true)) {
        continue;
    }

    $properties = extractTags(
        'relation',
        $r,
        $config['languages'],
        $config['instances'],
        $config['gender'] ?? [],
        $manualStreetsGender
    );

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
            printf(
                'Can\'t find way(%d) from relation(%d).%s',
                $street['ref'],
                $r['id'],
                PHP_EOL
            );
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

file_put_contents(
    sprintf('cities/%s/data/relations.geojson', $city),
    json_encode($geojson)
);

unset($json, $geojson);

// Process ways
$json = json_decode(file_get_contents('data/overpass/way/full.json'), true);

$nodes = extractElements('node', $json['elements']);
$ways = extractElements('way', $json['elements']);

$geojson = [
    'type'     => 'FeatureCollection',
    'features' => [],
];

foreach ($ways as $w) {
    if (isset($config['exclude'], $config['exclude']['way']) && is_array($config['exclude']['way']) && in_array($w['id'], $config['exclude']['way'], true)) {
        continue;
    }

    if (!in_array($w['id'], $waysInRelation)) {
        $properties = extractTags(
            'way',
            $w,
            $config['languages'],
            $config['instances'],
            $config['gender'] ?? [],
            $manualStreetsGender
        );

        $linestring = appendCoordinates($nodes, $w);

        $geojson['features'][] = [
            'type'       => 'Feature',
            'id'         => $w['id'],
            'properties' => $properties,
            'geometry'   => makeGeometry(
                is_null($linestring) ? null : [$linestring]
            ),
        ];
    }
}

file_put_contents(
    sprintf('cities/%s/data/ways.geojson', $city),
    json_encode($geojson)
);

unset($json, $geojson);

exit(0);
