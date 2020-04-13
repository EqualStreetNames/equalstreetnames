<?php

declare(strict_types=1);

chdir(__DIR__ . '/../');

require 'vendor/autoload.php';

$associatedStreets = json_decode(file_get_contents('data/overpass/relation/full.json'), true);
$highways = json_decode(file_get_contents('data/overpass/way/full.json'), true);

$elements = array_merge($associatedStreets['elements'], $highways['elements']);

$tagged = array_filter(
    $elements,
    function ($element) {
        return isset($element['tags']) &&
            (isset($element['tags']['wikidata']) || isset($element['tags']['name:etymology:wikidata']));
    }
);

foreach ($tagged as $element) {
    $wikidata = $element['tags']['wikidata'] ?? null;
    $etymology = $element['tags']['name:etymology:wikidata'] ?? null;

    // if (!is_null($wikidata)) {
    //     $path = sprintf('data/wikidata/%s.json', $wikidata);

    //     if (!file_exists($path)) {
    //         file_put_contents($path, get($wikidata));
    //     }
    // }

    if (!is_null($etymology)) {
        $etymology = explode(';', $etymology);
        $etymology = array_map('trim', $etymology);

        foreach ($etymology as $e) {
            if (preg_match('/^Q.+$/', $e) !== 1) {
                printf(
                    'Format of `name:etymology:wikidata` is invalid (%s) for %s(%d).%s',
                    $e,
                    $element['type'],
                    $element['id'],
                    PHP_EOL
                );
                continue;
            }

            $path = sprintf('data/wikidata/%s.json', $e);

            if (!file_exists($path)) {
                file_put_contents($path, get($e));
            }
        }
    }
}

exit(0);

function get(string $id): string
{
    $client = new \GuzzleHttp\Client();
    $response = $client->request(
        'GET',
        sprintf('https://www.wikidata.org/wiki/Special:EntityData/%s.json', $id)
    );

    $status = $response->getStatusCode();

    if ($status !== 200) {
        throw new ErrorException($response->getReasonPhrase());
    }

    return (string) $response->getBody();
}
