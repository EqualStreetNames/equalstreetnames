<?php

/**
 * Get the relevant relations from OpenStreetMap via Overpass API.
 */

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

if (file_exists('data/equalstreetnames.db')) {
    unlink('data/equalstreetnames.db');
}

$pdo = new PDO('sqlite:data/equalstreetnames.db');

// relevant relations
$pdo->exec('CREATE TABLE relation (municipality VARCHAR, id INTEGER, name VARCHAR, name_fr VARCHAR, name_nl VARCHAR, wikidata VARCHAR, etymology VARCHAR)');

$glob = glob('data/overpass/relation/*.csv');

foreach ($glob as $path) {
    $fname = pathinfo($path, PATHINFO_FILENAME);

    if (($handle = fopen($path, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
            $stmt = $pdo->prepare('INSERT INTO relation VALUES(:municipality, :id, :name, :name_fr, :name_nl, :wikidata, :etymology)');
            $stmt->execute(
                [
                    ':municipality' => $fname,
                    ':id'           => $data[1],
                    ':name'         => $data[2],
                    ':name_fr'      => $data[3],
                    ':name_nl'      => $data[4],
                    ':wikidata'     => $data[5],
                    ':etymology'    => $data[6],
                ]
            );
        }
        fclose($handle);
    }
}

$stmt = $pdo->query('SELECT COUNT(*) FROM relation');
$count = $stmt->fetchColumn(0);

$stmt = $pdo->query('SELECT COUNT(*) FROM relation WHERE wikidata <> \'\'');
$countWikidata = $stmt->fetchColumn(0);

$stmt = $pdo->query('SELECT COUNT(*) FROM relation WHERE etymology <> \'\'');
$countEtymology = $stmt->fetchColumn(0);

printf('%d relations%s', $count, PHP_EOL);
printf('%d relations with `wikidata` tag%s', $countWikidata, PHP_EOL);
printf('%d relations with `name:etymology:wikidata` tag%s', $countEtymology, PHP_EOL);

// highway ways
$pdo->exec('CREATE TABLE way (municipality VARCHAR, id INTEGER, name VARCHAR, name_fr VARCHAR, name_nl VARCHAR, wikidata VARCHAR, etymology VARCHAR)');

$glob = glob('data/overpass/way/*.csv');

foreach ($glob as $path) {
    $fname = pathinfo($path, PATHINFO_FILENAME);

    if (($handle = fopen($path, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
            $stmt = $pdo->prepare('INSERT INTO way VALUES(:municipality, :id, :name, :name_fr, :name_nl, :wikidata, :etymology)');
            $stmt->execute(
                [
                    ':municipality' => $fname,
                    ':id'           => $data[1],
                    ':name'         => $data[2],
                    ':name_fr'      => $data[3],
                    ':name_nl'      => $data[4],
                    ':wikidata'     => $data[5],
                    ':etymology'    => $data[6],
                ]
            );
        }
        fclose($handle);
    }
}

$stmt = $pdo->query('SELECT COUNT(*) FROM way');
$count = $stmt->fetchColumn(0);

$stmt = $pdo->query('SELECT COUNT(*) FROM way WHERE wikidata <> \'\'');
$countWikidata = $stmt->fetchColumn(0);

$stmt = $pdo->query('SELECT COUNT(*) FROM way WHERE etymology <> \'\'');
$countEtymology = $stmt->fetchColumn(0);

printf('%d ways%s', $count, PHP_EOL);
printf('%d ways with `wikidata` tag%s', $countWikidata, PHP_EOL);
printf('%d ways with `name:etymology:wikidata` tag%s', $countEtymology, PHP_EOL);

exit(0);
