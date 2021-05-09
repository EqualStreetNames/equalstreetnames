<?php

namespace App\Command;

use App\Model\GeoJSON\Feature;
use App\Model\GeoJSON\FeatureCollection;
use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate statistics and final CSV files.
 *   - `metadata.json` will contain last update datetime, sources statistics, and gender statistics
 *   - `gender.csv` will contain the listing of streets with the gender, source, and wikidata identifier(s) (if available)
 *   - `other.csv` will contain the listing of streets that are not related to a person
 *
 * @package App\Command
 */
class StatisticsCommand extends AbstractCommand
{
    /** {@inheritdoc} */
    protected static $defaultName = 'statistics';

    /** @var string Filename for the CSV file containing streets with "a gender". */
    public const FILENAME_GENDER_CSV = 'gender.csv';
    /** @var string Filename for the CSV file containing streets without "a gender". */
    public const FILENAME_OTHER_CSV = 'other.csv';
    /** @var string Filename for the metadata JSON file. */
    public const FILENAME_METADATA = 'metadata.json';

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Calculate statistics.');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            parent::execute($input, $output);

            // Check path of relations GeoJSON file.
            $relationPath = sprintf('%s/%s', $this->cityOutputDir, GeoJSONCommand::FILENAME_RELATION);
            if (!file_exists($relationPath) || !is_readable($relationPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "geojson" command first.', $relationPath));
            }
            // Check path of ways GeoJSON file.
            $wayPath = sprintf('%s/%s', $this->cityOutputDir, GeoJSONCommand::FILENAME_WAY);
            if (!file_exists($wayPath) || !is_readable($wayPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "geojson" command first.', $wayPath));
            }

            $streets = [];

            // Read GeoJSON files.
            $contentR = file_get_contents($relationPath);
            /** @var FeatureCollection|null */ $relations = $contentR !== false ? json_decode($contentR) : null;
            $contentW = file_get_contents($wayPath);
            /** @var FeatureCollection|null */ $ways = $contentW !== false ? json_decode($contentW) : null;

            // Extract necesarry data
            if (!is_null($relations)) {
                foreach ($relations->features as $feature) {
                    $street = self::extract($feature);
                    $street['type'] = 'relation';

                    $streets[] = $street;
                }
            }
            if (!is_null($ways)) {
                foreach ($ways->features as $feature) {
                    $street = self::extract($feature);
                    $street['type'] = 'way';

                    $streets[] = $street;
                }
            }

            // Group by streetname
            $streets = self::groupBy($streets, $output);

            // Sort streets
            $streets = self::sort($streets);

            // Export to CSV ("gender.csv" + "other.csv")
            self::exportCSV(
                sprintf('%s/%s', $this->cityOutputDir, self::FILENAME_GENDER_CSV),
                array_filter($streets, function ($street): bool {
                    return !is_null($street['gender']);
                })
            );
            self::exportCSV(
                sprintf('%s/%s', $this->cityOutputDir, self::FILENAME_OTHER_CSV),
                array_filter($streets, function ($street): bool {
                    return is_null($street['gender']);
                })
            );

            // Calculate statistics
            $genders = [
                'F'  => 0, // female (cis)
                'M'  => 0, // male (cis)
                'FX' => 0, // female (trans)
                'MX' => 0, // male (trans)
                'X'  => 0, // intersex
                'NB' => 0, // non-binary
                '+'  => 0, // multi (male + female)
                '?'  => 0, // unknown gender
                '-'  => 0, // not a person
            ];
            $sources = [
                'wikidata' => 0,
                'csv'      => 0,
                'config'   => 0,
                'event'    => 0,
                '-'        => 0,
            ];

            foreach ($streets as $street) {
                $genders[$street['gender'] ?? '-']++;

                if (is_null($street['source'])) {
                    $sources['-']++;
                } else {
                    $_sources = explode('+', $street['source']);
                    foreach ($_sources as $s) {
                        $sources[$s]++;
                    }
                }
            }

            $metadata = [
                'update'  => date('c'),
                'sources' => $sources,
                'genders' => $genders,
            ];

            // Store metadata
            file_put_contents(
                sprintf('%s/%s', $this->cityOutputDir, self::FILENAME_METADATA),
                json_encode($metadata, JSON_PRETTY_PRINT)
            );

            // Display statistics
            $total = $genders['F'] + $genders['M'] + $genders['FX'] + $genders['MX'] + $genders['X'] + $genders['NB'] + $genders['+'] + $genders['?'];

            $output->writeln([
                sprintf('Person: %d', $total),
                sprintf('  Female (cis): %d (%.2f %%)', $genders['F'], $genders['F'] / $total * 100),
                sprintf('  Male (cis): %d (%.2f %%)', $genders['M'], $genders['M'] / $total * 100),
                sprintf('  Female (trans): %d (%.2f %%)', $genders['FX'], $genders['FX'] / $total * 100),
                sprintf('  Male (trans): %d (%.2f %%)', $genders['MX'], $genders['MX'] / $total * 100),
                sprintf('  Intersex: %d (%.2f %%)', $genders['X'], $genders['X'] / $total * 100),
                sprintf('  Non-Binary: %d (%.2f %%)', $genders['NB'], $genders['NB'] / $total * 100),
                sprintf('  Multiple: %d (%.2f %%)', $genders['+'], $genders['+'] / $total * 100),
                sprintf('  Unknown: %d (%.2f %%)', $genders['?'], $genders['?'] / $total * 100),
                sprintf('Not a person: %d', $genders['-']),
            ]);

            $output->writeln('');

            $output->writeln([
                'Sources:',
                sprintf('  Wikidata: %d (%.2f %%)', $sources['wikidata'], $sources['wikidata'] / $total * 100),
                sprintf('  CSV: %d (%.2f %%)', $sources['csv'], $sources['csv'] / $total * 100),
                sprintf('  Configuration: %d (%.2f %%)', $sources['config'], $sources['config'] / $total * 100),
                sprintf('  Event (Brussels only): %d (%.2f %%)', $sources['event'], $sources['event'] / $total * 100),
                sprintf('No source: %d', $sources['-']),
            ]);

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * Extract data needed for statistics from GeoJSON Feature.
     *
     * @param Feature $feature GeoJSON Feature.
     *
     * @return array<string,mixed>
     */
    private static function extract($feature): array
    {
        if (!is_null($feature->properties->details)) {
            if (is_array($feature->properties->details)) {
                $wikidata = implode(';', array_column($feature->properties->details, 'wikidata'));
            } else {
                $wikidata = $feature->properties->details->wikidata ?? null;
            }
        }

        return [
            'id'       => $feature->id,
            'name'     => $feature->properties->name,
            'source'   => $feature->properties->source,
            'gender'   => $feature->properties->gender ?? null,
            'wikidata' => $wikidata ?? null,
        ];
    }

    /**
     * Remove accents from string.
     *
     * @param string $string
     * @param string $charset
     * @return string
     */
    private static function normalize(string $string, string $charset = 'utf-8'): string
    {
        $str = htmlentities($string, ENT_NOQUOTES, $charset);

        /** @var string */ $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        /** @var string */ $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        /** @var string */ $str = preg_replace('#&[^;]+;#', '', $str);

        // $str = strtolower($str);

        return $str;
    }

    /**
     * Sort streets array by wikidata, gender, and street name.
     *
     * @param array $streets
     * @return array
     */
    private static function sort(array $streets): array
    {
        $wikidata = array_map(
            function ($street): bool {
                return is_null($street['wikidata']);
            },
            $streets
        );

        $name = array_map(
            function ($street): string {
                return self::normalize($street['name']);
            },
            $streets
        );

        array_multisort(
            $wikidata,
            SORT_ASC,
            array_column($streets, 'gender'),
            SORT_ASC,
            $name,
            SORT_ASC,
            $streets
        );

        return $streets;
    }

    /**
     * Group streets by street name (using md5 hash of the normalized street name).
     *
     * @param array $streets
     * @param OutputInterface $output
     * @return array
     *
     * @throws Exception
     */
    private static function groupBy(array $streets, OutputInterface $output): array
    {
        // Group streets by streetname
        $groups = [];
        foreach ($streets as $street) {
            if (is_null($street['name'])) {
                throw new Exception('%s(%s): Streetname should not be NULL.', $street['type'], $street['id']);
            }

            $key = md5($street['name']);
            if (!isset($groups[$key])) {
                $groups[$key] = [];
            }
            $groups[$key][] = $street;
        }

        // Keep only one record per streetname
        $results = [];
        foreach ($groups as $_streets) {
            if (count($_streets) === 1) {
                $results[] = [
                    'name'     => $_streets[0]['name'],
                    'source'   => $_streets[0]['source'],
                    'gender'   => $_streets[0]['gender'],
                    'wikidata' => $_streets[0]['wikidata'],
                    'type'     => $_streets[0]['type'],
                ];
            } else {
                $types = array_unique(array_column($_streets, 'type'));
                $sources = array_values(array_filter(array_unique(array_column($_streets, 'source')), function ($value): bool {
                    return !is_null($value);
                }));
                $genders = array_values(array_filter(array_unique(array_column($_streets, 'gender')), function ($value): bool {
                    return !is_null($value);
                }));
                $wikidatas = array_values(array_filter(array_unique(array_column($_streets, 'wikidata')), function ($value): bool {
                    return !is_null($value);
                }));

                sort($types);
                sort($sources);
                sort($genders);
                sort($wikidatas);

                if (count($sources) > 1) {
                    // Temporary workaround (Brussels only)
                    if ($sources === ['event', 'wikidata']) {
                        $sources = ['wikidata'];
                    } elseif ($sources === ['config', 'event']) {
                        $sources = ['config'];
                    } else {
                        $output->writeln(sprintf('Multiple source (%s) for street "%s".', implode(', ', $sources), $_streets[0]['name']));
                    }
                }
                if (count($genders) > 1) {
                    $output->writeln(sprintf('<warning>Gender mismatch (%s) for street "%s".</warning>', implode(', ', $genders), $_streets[0]['name']));
                }
                if (count($wikidatas) > 1) {
                    $output->writeln(sprintf('<warning>Wikidata mismatch (%s) for street "%s".</warning>', implode(', ', $wikidatas), $_streets[0]['name']));
                }

                $results[] = [
                    'name'     => $_streets[0]['name'],
                    'source'   => count($sources) === 0 ? null : implode('+', $sources),
                    'gender'   => count($genders) === 0 ? null : (count($genders) > 1 ? '?' : $genders[0]),
                    'wikidata' => count($wikidatas) === 0 ? null : implode(';', $wikidatas),
                    'type'     => /*count($types) > 1 ? implode('+', $types) : */ $types[0],
                ];
            }
        }

        return $results;
    }

    /**
     * Write streets array in CSV File (with header).
     *
     * @param string $path Path of the resulting CSV file.
     * @param array $streets
     * @return void
     */
    private static function exportCSV(string $path, array $streets): void
    {
        $fp = fopen($path, 'w');

        if ($fp !== false) {
            fputcsv($fp, ['name', 'source', 'gender', 'wikidata', 'type']);

            foreach ($streets as $street) {
                fputcsv($fp, $street);
            }

            fclose($fp);
        }
    }
}
