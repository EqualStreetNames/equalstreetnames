<?php

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

$relations = json_decode(file_get_contents('static/relations.geojson'), true);
$ways = json_decode(file_get_contents('static/ways.geojson'), true);

$statistics = [
    'f' => [],
    'm' => [],
    'x' => [],
    '-' => [],
];

// JSON file

foreach ($relations['features'] as $feature) {
    $gender = isset($feature['properties']['person']) ? strtolower($feature['properties']['person']['gender']) ?? '-' : '-';
    $name = $feature['properties']['name'];

    if (!in_array($name, $statistics[$gender])) {
        $statistics[$gender][] = $name;
    }
}

foreach ($ways['features'] as $feature) {
    $gender = isset($feature['properties']['person']) ? strtolower($feature['properties']['person']['gender']) ?? '-' : '-';
    $name = $feature['properties']['name'];

    if (!in_array($name, $statistics[$gender])) {
        $statistics[$gender][] = $name;
    }
}

file_put_contents('static/statistics.json', json_encode($statistics));

// CSV file

$fp = fopen('static/gender.csv', 'w');

foreach (['f', 'm', 'x'] as $gender) {
    foreach ($statistics[$gender] as $streetname) {
        fputcsv(
            $fp,
            [$streetname, strtoupper($gender)]
        );
    }
}

fclose($fp);


$total = count($statistics['f']) + count($statistics['m']) + count($statistics['x'])/* + count($statistics['-'])*/;

printf('Female: %d (%.2f %%)%s', count($statistics['f']), count($statistics['f']) / $total * 100, PHP_EOL);
printf('Male: %d (%.2f %%)%s', count($statistics['m']), count($statistics['m']) / $total * 100, PHP_EOL);
printf('Other: %d (%.2f %%)%s', count($statistics['x']), count($statistics['x']) / $total * 100, PHP_EOL);
printf('No person: %d%s', count($statistics['-']), PHP_EOL);

exit(0);
