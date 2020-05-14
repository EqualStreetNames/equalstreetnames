<?php

declare(strict_types=1);

use GuzzleHttp\Exception\BadResponseException;

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

$directory = 'data/wikidata';

if (!file_exists($directory) || !is_dir($directory)) {
    mkdir($directory, 0777, true);
}

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

            $path = sprintf('%s/%s.json', $directory, $e);

            if (!file_exists($path)) {
                $client = new \GuzzleHttp\Client();

                try {
                    $response = $client->request(
                        'GET',
                        sprintf('https://www.wikidata.org/wiki/Special:EntityData/%s.json', $e),
                        [
                            'sink' => $path,
                        ]
                    );
                } catch (BadResponseException $exception) {
                    if ($exception->getCode() === 404) {
                        printf(
                            'Wikidata item %s for %s(%d) does not exist.%s',
                            $e,
                            $element['type'],
                            $element['id'],
                            PHP_EOL
                        );
                    } else {
                        printf(
                            'Error while fetching Wikidata item %s for %s(%d): %s.%s',
                            $e,
                            $element['type'],
                            $element['id'],
                            $exception->getMessage(),
                            PHP_EOL
                        );
                    }
                }
            }
        }
    }
}

exit(0);
