<?php

/**
 * Get the relevant ways from OpenStreetMap via Overpass API.
 */

declare(strict_types=1);

chdir(__DIR__ . '/../../');

require 'vendor/autoload.php';

$directory = 'data/overpass/way';

if (!file_exists($directory) || !is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$options = getopt('c:', ['city:']);

$city = $options['c'] ?? $options['city'];

// Get all the relevant relations in Brussels Region.
file_put_contents(
    sprintf('%s/full.json', $directory),
    get($city)
);

exit(0);

/**
 * Run Overpass API.
 *
 * @return string JSON response from Overpass.
 */
function get(string $city): string
{
    $query = file_get_contents(
        sprintf('cities/%s/overpass/way-full-json', $city)
    );
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
