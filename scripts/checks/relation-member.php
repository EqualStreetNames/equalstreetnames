<?php

declare(strict_types=1);

chdir(__DIR__.'/../../');

require 'vendor/autoload.php';

$relations = json_decode(file_get_contents('data/overpass/relation/full.json'));
$ways = json_decode(file_get_contents('data/overpass/way/full.json'));

foreach ($relations->elements as $relation) {
    if (!isset($relations->tags->wikidata) && !isset($relation->tags->{'name:etymology:wikidata'})) {
        continue;
    }

    $members = array_filter(
        $relation->members,
        function ($member) {
            return $member->type === 'way' && $member->role === 'street';
        }
    );

    foreach ($members as $member) {
        $id = $member->ref;

        $way = array_filter(
            $ways->elements,
            function ($way) use ($id) {
                return $way->id === $id;
            }
        );

        if (count($way) === 0) {
            // printf('Couldn\'t find way(%d) from relation(%d).%s', $id, $relation->id, PHP_EOL);
        } elseif (count($way) > 1) {
            printf('Multiple match for way(%d) from relation(%d).%s', $id, $relation->id, PHP_EOL);
        } else {
            $way = current($way);

            if (($relation->tags->wikidata ?? null) !== ($way->tags->wikidata ?? null)) {
                printf('Mismatch `wikidata` for way(%d) from relation(%d).%s', $id, $relation->id, PHP_EOL);
            }
            if (($relation->tags->{'name:etymology:wikidata'} ?? null) !== ($way->tags->{'name:etymology:wikidata'} ?? null)) {
                printf('Mismatch `name:etymology:wikidata` for way(%d) from relation(%d).%s', $id, $relation->id, PHP_EOL);
            }
        }
    }
}

exit();
