<?php

declare(strict_types=1);

/**
 * Transform way and array of nodes into LineString.
 *
 * @param array $nodes Array of nodes (with coordinates).
 * @param array $way   Way (with nodes id).
 *
 * @return array|null
 */
function appendCoordinates(array $nodes, array $way): ?array
{
    $linestring = [];

    foreach ($way['nodes'] as $id) {
        $node = $nodes[$id] ?? null;

        if (is_null($node)) {
            printf('Can\'t find node(%d) in way(%d).%s', $id, $way['id'], PHP_EOL);
        } else {
            $linestring[] = [
                $node['lon'],
                $node['lat'],
            ];
        }
    }

    if (count($linestring) === 0) {
        printf('No geometry for way(%d).%s', $way['id'], PHP_EOL);
    }

    return count($linestring) === 0 ? null : $linestring;
}

/**
 * Create MultiLineString (if necessary) from LineString coordinates array.
 *
 * @param array $linestrings Array of LineString coordinates array.
 *
 * @return array|null
 */
function makeGeometry(array $linestrings): ?array
{
    if (count($linestrings) === 0) {
        return null;
    } elseif (count($linestrings) > 1) {
        return [
            'type'        => 'MultiLineString',
            'coordinates' => $linestrings,
        ];
    } else {
        return [
            'type'        => 'LineString',
            'coordinates' => $linestrings[0],
        ];
    }
}
