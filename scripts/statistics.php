<?php

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';
require 'library/statistics.php';

$options = getopt('c:', ['city:']);

$city = $options['c'] ?? $options['city'];

$relations = json_decode(
    file_get_contents(
        sprintf('cities/%s/data/relations.geojson', $city)
    )
);
$ways = json_decode(
    file_get_contents(
        sprintf('cities/%s/data/ways.geojson', $city)
    )
);

$streets = [];
$count = [
    'F'  => 0, // female (cis)
    'M'  => 0, // male (cis)
    'FX' => 0, // female (trans)
    'MX' => 0, // male (trans)
    'X'  => 0, // intersex
    '+'  => 0, // multi (male + female)
    '?'  => 0, // unknown gender
    '-'  => 0, // not a person
];

foreach ($relations->features as $feature) {
    $data = extractData('relation', $feature, $streets);

    if ($data !== false) {
        $streets[] = $data;

        $count[is_null($data['gender']) ? '-' : $data['gender']]++;
    }
}
foreach ($ways->features as $feature) {
    $data = extractData('way', $feature, $streets);

    if ($data !== false) {
        $streets[] = $data;

        $count[is_null($data['gender']) ? '-' : $data['gender']]++;
    }
}

$wikidata = array_map(
    function ($street) {
        return is_null($street['wikidata']);
    },
    $streets
);
$name = array_map(
    function ($street) {
        return removeAccents($street['name']);
    },
    $streets
);

array_multisort(
    $wikidata,
    SORT_ASC,
    array_column($streets, 'gender'),
    SORT_ASC,
    $name,
    SORT_ASC,
    $streets
);

// CSV file

$previous = null;

$fp = fopen(sprintf('cities/%s/data/gender.csv', $city), 'w');
$fp2 = fopen(sprintf('cities/%s/data/other.csv', $city), 'w');

fputcsv($fp, ['name', 'gender', 'wikidata', 'type']);
fputcsv($fp2, ['name', 'gender', 'wikidata', 'type']);

foreach ($streets as $street) {
    if (in_array($street['gender'], ['F', 'M', 'FX', 'MX', 'X', '+', '?'])) {
        fputcsv($fp, $street);
    } else {
        fputcsv($fp2, $street);
    }

    $previous = $street;
}

fclose($fp);
fclose($fp2);

// JSON file

file_put_contents(sprintf('cities/%s/data/statistics.json', $city), json_encode($count));

echo PHP_EOL;

$total = $count['F'] + $count['M'] + $count['FX'] + $count['MX'] + $count['X'] + $count['+'] + $count['?'];

printf('Person: %d%s', $total, PHP_EOL);
printf('Female (cis): %d (%.2f %%)%s', $count['F'], $count['F'] / $total * 100, PHP_EOL);
printf('Male (cis): %d (%.2f %%)%s', $count['M'], $count['M'] / $total * 100, PHP_EOL);
printf('Female (transgender): %d (%.2f %%)%s', $count['FX'], $count['FX'] / $total * 100, PHP_EOL);
printf('Male (transgender): %d (%.2f %%)%s', $count['MX'], $count['MX'] / $total * 100, PHP_EOL);
printf('Intersex: %d (%.2f %%)%s', $count['X'], $count['X'] / $total * 100, PHP_EOL);
printf('Multiple: %d (%.2f %%)%s', $count['+'], $count['+'] / $total * 100, PHP_EOL);
printf('Unknown: %d (%.2f %%)%s', $count['?'], $count['?'] / $total * 100, PHP_EOL);

echo PHP_EOL;

printf('Not a person: %d%s', $count['-'], PHP_EOL);

exit(0);
