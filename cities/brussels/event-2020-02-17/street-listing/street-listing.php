<?php

/**
 * Get the full street listing (relation + way) by municipality.
 */

declare(strict_types=1);

chdir(__DIR__.'/../');

require 'vendor/autoload.php';

$pdo = new PDO('sqlite:data/equalstreetnames.db');

$municipalities = include 'scripts/municipalities.php';

foreach ($municipalities as $nis5 => $municipality) {
    $mun = strtolower($municipality[0]);

    $fp = fopen(sprintf('data/%s.csv', $mun), 'w');
    fputcsv(
        $fp,
        [
            'type',
            'id',
            'nom de rue',
            'straatnaam',
            // 'wikidata (rue/straat)',
            'wikidata (personne/persoon)',
        ]
    );

    $stmt = $pdo->prepare('SELECT * FROM relation WHERE municipality = :mun');
    $stmt->execute(['mun' => $mun]);
    $relations = $stmt->fetchAll();

    foreach ($relations as $r) {
        fputcsv(
            $fp,
            [
                'relation',
                $r['id'],
                $r['name_fr'],
                $r['name_nl'],
                // $r['wikidata'],
                $r['etymology'],
            ]
        );
    }

    $stmt = $pdo->prepare('SELECT name_fr,name_nl,wikidata,etymology FROM way WHERE municipality = :mun AND (name_fr <> \'\' OR name_nl <> \'\') AND name NOT IN(SELECT name FROM relation WHERE municipality = :mun) GROUP BY municipality,name ORDER BY municipality,name;');
    $stmt->execute(['mun' => $mun]);
    $ways = $stmt->fetchAll();

    foreach ($ways as $w) {
        fputcsv(
            $fp,
            [
                'way',
                '',
                $w['name_fr'],
                $w['name_nl'],
                // $w['wikidata'],
                $w['etymology'],
            ]
        );
    }

    fclose($fp);
}

exit(0);
