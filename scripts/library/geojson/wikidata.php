<?php

declare(strict_types=1);

/**
 * Extract useful data from Wikidata.
 *
 * @param string   $identifier Wikidata identifier.
 * @param string[] $languages  Required languages.
 * @param array    $instances  Array that defines what instances are
 *                             considered as a person.
 *
 * @return array|null
 */
function extractWikidata(
    string $identifier,
    array $languages,
    array $instances
): ?array {
    $path = sprintf('data/wikidata/%s.json', $identifier);

    if (!file_exists($path)) {
        printf('Missing file for %s.%s', $identifier, PHP_EOL);

        return null;
    }

    $json = json_decode(file_get_contents($path), true);

    $entity = $json['entities'][$identifier] ?? null;

    if (is_null($entity)) {
        printf(
            'Entity %s missing in "%s".%s',
            $identifier,
            basename($path),
            PHP_EOL
        );

        return null;
    }

    $instance = $entity['claims']['P31'] ?? $entity['claims']['P279'] ?? null;

    $person = false;
    if (is_null($instance)) {
        printf('No instance or subclass for %s.%s', $identifier, PHP_EOL);
    } else {
        foreach ($instance as $p) {
            $value = $p['mainsnak']['datavalue']['value']['id'];
            if (isset($instances[$value])) {
                if ($instances[$value] === true) {
                    $person = true;
                    break;
                }
            } else {
                printf('New instance %s for %s.%s', $value, $identifier, PHP_EOL);
            }
        }
    }

    $labels = array_filter(
        $entity['labels'],
        function ($language) use ($languages) {
            return in_array($language, $languages);
        },
        ARRAY_FILTER_USE_KEY
    );

    $descriptions = array_filter(
        $entity['descriptions'],
        function ($language) use ($languages) {
            return in_array($language, $languages);
        },
        ARRAY_FILTER_USE_KEY
    );

    $nicknames = null;
    if (isset($entity['claims']['P1449'])) {
        foreach ($entity['claims']['P1449'] as $value) {
            $language = $value['mainsnak']['datavalue']['value']['language'];

            if (in_array($language, $languages, true)) {
                $nicknames[$language] = $value['mainsnak']['datavalue']['value'];
            }
        }
    }

    $sitelinks = array_filter(
        $entity['sitelinks'],
        function ($language) use ($languages) {
            return in_array(
                $language,
                array_map(
                    function ($language) {
                        return $language.'wiki';
                    },
                    $languages
                )
            );
        },
        ARRAY_FILTER_USE_KEY
    );

    $genderId = $entity['claims']['P21'][0]['mainsnak']['datavalue']['value']['id'] ?? null;

    $dateOfBirth = $entity['claims']['P569'][0]['mainsnak']['datavalue']['value']['time'] ?? null;
    $dateOfDeath = $entity['claims']['P570'][0]['mainsnak']['datavalue']['value']['time'] ?? null;

    return [
        'wikidata'     => $identifier,
        'person'       => $person,
        'labels'       => $labels,
        'descriptions' => $descriptions,
        'nicknames'    => $nicknames,
        'gender'       => is_null($genderId) ? null : extractGender($genderId),
        'birth'        => is_null($dateOfBirth) ? null : intval(substr($dateOfBirth, 1, 4)),
        'death'        => is_null($dateOfDeath) ? null : intval(substr($dateOfDeath, 1, 4)),
        'sitelinks'    => $sitelinks,
    ];
}

/**
 * Transform Wikidata identifier to "gender".
 *
 * @param string $identifier Wikidata identifier.
 *
 * @return string|null
 */
function extractGender(string $identifier): ?string
{
    switch ($identifier) {
        case 'Q6581097': // male
        case 'Q15145778': // male (cis)

            return 'M';

        case 'Q6581072': // female
        case 'Q15145779': // female (cis)

            return 'F';

        case 'Q1052281': // female (trans)

            return 'FX';

        case 'Q2449503': // male (trans)

            return 'MX';

        case 'Q1097630': // intersex

            return 'X';

        default:
            printf('Undefined gender %s.%s', $identifier, PHP_EOL);

            return null;
    }
}
