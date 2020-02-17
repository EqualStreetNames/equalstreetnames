<?php

/**
 * Get the associatedStreet relations from OpenStreetMap via Overpass API.
 */

declare(strict_types=1);

chdir(__DIR__ . '/../../');

require 'vendor/autoload.php';

$municipalities = include 'scripts/municipalities.php';

$directory = 'data/overpass/associatedStreet';

if (!file_exists($directory) || !is_dir($directory)) {
    mkdir($directory);
}

// Get all the associatedStreet relations in all the municipalities in Brussels Region.
foreach ($municipalities as $nis5 => $municipality) {
    printf('%d - %s%s', $nis5, $municipality[0], PHP_EOL);

    file_put_contents(
        sprintf('%s/%s.csv', $directory, strtolower($municipality[0])),
        get($nis5)
    );

    sleep(mt_rand(30, 120));
}

// Get all the associatedStreet relations in Brussels Region.
printf('FULL%s', PHP_EOL);

file_put_contents(
    sprintf('%s/full.json', $directory),
    get()
);

exit(0);

/**
 * Run Overpass API.
 *
 * @param int|null $nis5 NIS code of the municipality (or null to have the whole Brussels Region).
 *
 * @return string CSV or JSON response from Overpass.
 */
function get(?int $nis5 = null): string
{
    if (is_null($nis5)) {
        $query = file_get_contents('scripts/overpass/associatedStreet-full-json');
    } else {
        $query = file_get_contents('scripts/overpass/associatedStreet-municipality-csv');
        $query = str_replace('#####', $nis5, $query);
    }
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
