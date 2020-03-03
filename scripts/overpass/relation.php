<?php

/**
 * Get the relevant relations from OpenStreetMap via Overpass API.
 */

declare(strict_types=1);

chdir(__DIR__.'/../../');

require 'vendor/autoload.php';

$municipalities = include 'scripts/municipalities.php';

$directory = 'data/overpass/relation';

if (!file_exists($directory) || !is_dir($directory)) {
    mkdir($directory);
}

// Get all the relevant relations in Brussels Region.
file_put_contents(
    sprintf('%s/full.json', $directory),
    get()
);

exit(0);

/**
 * Run Overpass API.
 *
 * @return string JSON response from Overpass.
 */
function get(): string
{
    $query = file_get_contents('scripts/overpass/relation-full-json');
    $query = str_replace(["\r", "\n"], '', $query);

    $client = new \GuzzleHttp\Client();
    $response = $client->request(
        'GET',
        sprintf('https://overpass-api.de/api/interpreter?data=%s', urlencode($query))
    );

    $status = $response->getStatusCode();

    if ($status !== 200) {
        throw new ErrorException($response->getReasonPhrase());
    }

    return (string) $response->getBody();
}
