<?php

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

$relations = json_decode(file_get_contents('static/relations.geojson'), true);
$ways = json_decode(file_get_contents('static/ways.geojson'), true);

$streets = [];
$count = [
    'F'  => 0,
    'M'  => 0,
    'FX' => 0,
    'MX' => 0,
    'X'  => 0,
    '-'  => 0,
];

function wd_remove_accents($str, $charset = 'utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    $str = preg_replace('#&[^;]+;#', '', $str);

    return $str;
}

function already(string $name_fr, string $name_nl, ?string $gender, array $streets)
{
    $filter = array_filter(
        $streets,
        function (array $street) use ($name_fr, $name_nl, $gender) {
            return wd_remove_accents($street['name_fr']) == wd_remove_accents($name_fr)
                && wd_remove_accents($street['name_nl']) == wd_remove_accents($name_nl)
                && $street['gender'] == $gender;
        }
    );

    return count($filter) > 0 ? current($filter) : false;
}

foreach ($relations['features'] as $feature) {
    $person = isset($feature['properties']['details']) ? $feature['properties']['details']['person'] : false;

    $data = [
        'name_fr'  => $feature['properties']['name:fr'] ?? $feature['properties']['name'],
        'name_nl'  => $feature['properties']['name:nl'] ?? $feature['properties']['name'],
        'gender'   => $person === true ? $feature['properties']['details']['gender'] ?? null : null,
        'wikidata' => $feature['properties']['etymology'],
        'type'     => 'relation',
    ];

    if (($street = already($data['name_fr'], $data['name_nl'], $data['gender'], $streets)) === false) {
        $streets[] = $data;

        $count[is_null($data['gender']) ? '-' : $data['gender']]++;
    } elseif (!is_null($street['wikidata']) && !is_null($data['wikidata']) && $street['wikidata'] !== $data['wikidata']) {
        printf('Wikidata mismatch: %s - %s : %s <> %s%s', $data['name_fr'], $data['name_nl'], $data['wikidata'], $street['wikidata'], PHP_EOL);
    }
}

foreach ($ways['features'] as $feature) {
    $person = isset($feature['properties']['details']) ? $feature['properties']['details']['person'] : false;

    $data = [
        'name_fr'  => $feature['properties']['name:fr'] ?? $feature['properties']['name'],
        'name_nl'  => $feature['properties']['name:nl'] ?? $feature['properties']['name'],
        'gender'   => $person === true ? $feature['properties']['details']['gender'] ?? null : null,
        'wikidata' => $feature['properties']['etymology'],
        'type'     => 'way',
    ];

    if (($street = already($data['name_fr'], $data['name_nl'], $data['gender'], $streets)) === false) {
        $streets[] = $data;

        $count[is_null($data['gender']) ? '-' : $data['gender']]++;
    } elseif (!is_null($street['wikidata']) && !is_null($data['wikidata']) && $street['wikidata'] !== $data['wikidata']) {
        printf('Wikidata mismatch: %s - %s : %s <> %s%s', $data['name_fr'], $data['name_nl'], $data['wikidata'], $street['wikidata'], PHP_EOL);
    }
}

$wikidata = array_map(
    function ($street) {
        return is_null($street['wikidata']);
    },
    $streets
);
$name_fr = array_map(
    function ($street) {
        return wd_remove_accents($street['name_fr']);
    },
    $streets
);
$name_nl = array_map(
    function ($street) {
        return wd_remove_accents($street['name_nl']);
    },
    $streets
);

array_multisort(
    $wikidata,
    SORT_ASC,
    array_column($streets, 'gender'),
    SORT_ASC,
    $name_fr,
    SORT_ASC,
    $name_nl,
    SORT_ASC,
    $streets
);

// CSV file

$previous = null;

$fp = fopen('static/gender.csv', 'w');
$fp2 = fopen('static/other.csv', 'w');

fputcsv($fp, ['name_fr', 'name_nl', 'gender', 'wikidata', 'type']);
fputcsv($fp2, ['name_fr', 'name_nl', 'gender', 'wikidata', 'type']);

foreach ($streets as $street) {
    if (in_array($street['gender'], ['F', 'M', 'FX', 'MX', 'X'])) {
        if (!is_null($previous) && ($previous['name_fr'] == $street['name_fr'] || $previous['name_nl'] == $street['name_nl'])) {
            printf('Duplicate: %s - %s <> %s - %s%s', $previous['name_fr'], $previous['name_nl'], $street['name_fr'], $street['name_nl'], PHP_EOL);
        }

        fputcsv($fp, $street);
    } else {
        fputcsv($fp2, $street);
    }

    $previous = $street;
}

fclose($fp);
fclose($fp2);

// JSON file

file_put_contents('static/statistics.json', json_encode($count));

echo PHP_EOL;

$total = $count['F'] + $count['M'] + $count['FX'] + $count['MX'] + $count['X'];

printf('Person: %d%s', $total, PHP_EOL);
printf('Female (cis): %d (%.2f %%)%s', $count['F'], $count['F'] / $total * 100, PHP_EOL);
printf('Male (cis): %d (%.2f %%)%s', $count['M'], $count['M'] / $total * 100, PHP_EOL);
printf('Female (transgender): %d (%.2f %%)%s', $count['FX'], $count['FX'] / $total * 100, PHP_EOL);
printf('Male (transgender): %d (%.2f %%)%s', $count['MX'], $count['MX'] / $total * 100, PHP_EOL);
printf('Intersex: %d (%.2f %%)%s', $count['X'], $count['X'] / $total * 100, PHP_EOL);

echo PHP_EOL;

printf('Not a person: %d%s', $count['-'], PHP_EOL);

exit(0);
