<?php

declare(strict_types=1);

/**
 * Extract gender from manual work (only available for Brussels).
 *
 * @param array  $manualStreetsGender Manually assigned street gender
 * @param string $nameFr              Streetname (FR).
 * @param string $nameNl              Streetname (NL).
 *
 * @return string|null Gender.
 */
function getGender(
    array $manualStreetsGender,
    string $nameFr,
    string $nameNl
): ?string {
    $filter = array_filter(
        $manualStreetsGender,
        function ($street) use ($nameFr, $nameNl): bool {
            return $street[0] === $nameFr || $street[1] === $nameNl;
        }
    );

    if (count($filter) === 0) {
        return null;
    } elseif (count($filter) === 1) {
        $street = current($filter);

        return $street[2];
    } else {
        $gender = [];
        foreach ($filter as $street) {
            if (!in_array($street[2], $gender, true)) {
                $gender[] = $street[2];
            }
        }

        if (count($gender) === 0) {
            return null;
        } elseif (count($gender) === 1) {
            return current($gender);
        } else {
            printf(
                'Ambiguous gender for street "%s - %s".%s',
                $nameFr,
                $nameNl,
                PHP_EOL
            );

            return null;
        }
    }
}
