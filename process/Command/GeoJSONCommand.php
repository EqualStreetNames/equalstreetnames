<?php

namespace App\Command;

use App\Model\Config\Config;
use App\Model\Details\Details;
use App\Model\GeoJSON\Feature;
use App\Model\GeoJSON\FeatureCollection;
use App\Model\GeoJSON\Geometry\LineString;
use App\Model\GeoJSON\Geometry\MultiLineString;
use App\Model\GeoJSON\Properties;
use App\Model\Overpass\Element;
use App\Model\Overpass\Node;
use App\Model\Overpass\Overpass;
use App\Model\Overpass\Relation;
use App\Model\Overpass\Way;
use App\Model\Wikidata\Entity;
use App\Wikidata\Wikidata;
use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate consolidated GeoJSON files (geometry from _OpenStreetMap_ + data from _Wikidata_).
 *
 * @package App\Command
 */
class GeoJSONCommand extends AbstractCommand
{
    /** {@inheritdoc} */
    protected static $defaultName = 'geojson';

    /** @var string Filename of the CSV file. */
    public const FILENAME_CSV = 'data.csv';
    /** @var string Filename for the relations GeoJSON file. */
    public const FILENAME_RELATION = 'relations.geojson';
    /** @var string Filename for the ways GeoJSON file. */
    public const FILENAME_WAY = 'ways.geojson';

    /** @var array<string,string> Data from event CSV file (Brussels only). */
    protected array $event;

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

        $this->setDescription('Generate GeoJSON files.');
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

            // Process CSV file from event - Brussels only.
            if ($this->city === 'belgium/brussels') {
                $eventPath = sprintf('%s/event-2020-02-17/gender.csv', $this->cityDir);
                if (file_exists($eventPath) && is_readable($eventPath)) {
                    $this->event = [];
                    $handle = fopen(sprintf('%s/event-2020-02-17/gender.csv', $this->cityDir), 'r');
                    if ($handle !== false) {
                        while (($data = fgetcsv($handle)) !== false) {
                            $streetFR = $data[0];
                            $streetNL = $data[1];
                            $gender = $data[2];

                            if (isset($this->event[md5($streetFR)]) && $this->event[md5($streetFR)] !== $gender) {
                                throw new ErrorException('');
                            }
                            if (isset($this->event[md5($streetNL)]) && $this->event[md5($streetNL)] !== $gender) {
                                throw new ErrorException('');
                            }

                            $this->event[md5($streetFR)] = $gender;
                            $this->event[md5($streetNL)] = $gender;
                        }
                        fclose($handle);
                    }
                }
            }

            // Read CSV file.
            $csvPath = sprintf('%s/%s', $this->cityDir, self::FILENAME_CSV);
            if (file_exists($csvPath) && is_readable($csvPath)) {
                if (($handle = fopen($csvPath, 'r')) !== false) {
                    while (($data = fgetcsv($handle, 1000)) !== false) {
                        $this->csv[] = [
                            'type'        => $data[0],
                            'id'          => intval($data[1]),
                            'name'        => $data[2],
                            'gender'      => $data[3],
                            'person'      => $data[4],
                            'description' => $data[5],
                        ];
                    }
                    fclose($handle);
                }
            }

            // Check path of Overpass query result for relations.
            $relationPath = sprintf('%s/overpass/%s', self::OUTPUTDIR, OverpassCommand::FILENAME_RELATION);
            if (!file_exists($relationPath) || !is_readable($relationPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "overpass" command first.', $relationPath));
            }
            // Check path of Overpass query result for ways.
            $wayPath = sprintf('%s/overpass/%s', self::OUTPUTDIR, OverpassCommand::FILENAME_WAY);
            if (!file_exists($wayPath) || !is_readable($wayPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "overpass" command first.', $wayPath));
            }

            // Read Overpass queries result.
            $contentR = file_get_contents($relationPath);
            /** @var Overpass */ $overpassR = $contentR !== false ? json_decode($contentR) : null;
            $contentW = file_get_contents($wayPath);
            /** @var Overpass */ $overpassW = $contentW !== false ? json_decode($contentW) : null;

            // Generate consolidated GeoJSON files (OpenStreetMap + Wikidata).
            $output->write('Relations: ');
            $geojsonR = $this->createGeoJSON('relation', $overpassR->elements ?? [], $output);
            $output->write('Ways: ');
            $geojsonW = $this->createGeoJSON('way', $overpassW->elements ?? [], $output);

            // Filter out ways that are already relation members.
            $waysInRelation = array_map(function ($element): int {
                return $element->id;
            }, self::extractElements('way', $overpassR->elements ?? []));
            $features = array_filter($geojsonW->features, function (Feature $feature) use ($waysInRelation): bool {
                return !in_array($feature->id, $waysInRelation, true);
            });
            $geojsonW->features = array_values($features);

            // Filter out relations based on identifiers defined in `config.php`.
            if (isset($this->config->exclude, $this->config->exclude->relation)) {
                $features = array_filter($geojsonR->features, function (Feature $feature): bool {
                    return !in_array($feature->id, $this->config->exclude->relation, true);
                });
                $geojsonR->features = array_values($features);
            }
            // Filter out ways based on identifiers defined in `config.php`.
            if (isset($this->config->exclude, $this->config->exclude->way)) {
                $features = array_filter($geojsonW->features, function (Feature $feature): bool {
                    return !in_array($feature->id, $this->config->exclude->way, true);
                });
                $geojsonW->features = array_values($features);
            }

            // Check GeoJSON features count.
            if (count($geojsonR->features) === 0) {
                $output->writeln('<warning>No relation feature.</warning>');
            }
            if (count($geojsonW->features) === 0) {
                $output->writeln('<warning>No way feature.</warning>');
            }
            if (count($geojsonR->features) === 0 && count($geojsonW->features) === 0) {
                throw new ErrorException('No feature at all!');
            }

            // Store consolidated GeoJSON files.
            file_put_contents(
                sprintf('%s/%s', $this->cityOutputDir, self::FILENAME_RELATION),
                json_encode($geojsonR)
            );
            file_put_contents(
                sprintf('%s/%s', $this->cityOutputDir, self::FILENAME_WAY),
                json_encode($geojsonW)
            );

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * Extract OpenStreetMap elements based on type (relation/way/node) and return associative array where key is element identifier.
     *
     * @param string $type OpenStreetMap element type (relation/way/node).
     * @param Element[] $elements OpenStreetMap elements (relation/way/node).
     * @return Element[]
     */
    private static function extractElements(string $type, array $elements): array
    {
        $filter = array_filter(
            $elements,
            function ($element) use ($type): bool {
                return $element->type === $type;
            }
        );

        $result = [];

        foreach ($filter as $f) {
            $result[$f->id] = $f;
        }

        return $result;
    }

    /**
     * Extract needed details from Wikidata item.
     *
     * @param Entity $entity Wikidata item.
     * @param string[] $warnings
     * @return Details
     */
    private function extractDetailsFromWikidata($entity, array &$warnings = []): Details
    {
        $dateOfBirth = Wikidata::extractDateOfBirth($entity);
        $dateOfDeath = Wikidata::extractDateOfDeath($entity);

        $person = Wikidata::isPerson($entity, $this->config->instances);
        if (is_null($person)) {
            $warnings[] = sprintf('No instance or subclass for "%s".', $entity->id);
            $person = false;
        }

        return new Details([
            'wikidata'     => $entity->id,
            'person'       => $person,
            'gender'       => Wikidata::extractGender($entity),
            'labels'       => Wikidata::extractLabels($entity, $this->config->languages),
            'descriptions' => Wikidata::extractDescriptions($entity, $this->config->languages),
            'nicknames'    => Wikidata::extractNicknames($entity, $this->config->languages),
            'birth'        => is_null($dateOfBirth) ? null : intval(substr($dateOfBirth, 0, 5)),
            'death'        => is_null($dateOfDeath) ? null : intval(substr($dateOfDeath, 0, 5)),
            'sitelinks'    => Wikidata::extractSitelinks($entity, $this->config->languages),
            'image'        => Wikidata::extractImage($entity),
        ]);
    }

    /**
     * Extract details from CSV file.
     *
     * @param Element $object OpenStreetMap element (relation/way/node).
     * @param array $warnings
     * @return null|Details
     */
    private function extractDetailsFromCSV($object, array &$warnings = []): ?Details
    {
        $records = array_filter($this->csv, function ($r) use ($object): bool {
            return $r['type'] === $object->type && $r['id'] === $object->id;
        });

        if (count($records) === 0) {
            return null;
        }
        if (count($records) > 1) {
            $warnings[] = sprintf('Duplicated record of %s(%s) in CSV file.', $object->type, $object->id);
        }

        $record = current($records);

        $labels = [];
        foreach ($this->config->languages as $lang) {
            $labels[$lang] = ['language' => $lang, 'value' => $record['person']];
        }
        $descriptions = [];
        foreach ($this->config->languages as $lang) {
            $descriptions[$lang] = ['language' => $lang, 'value' => $record['description']];
        }

        return new Details([
            'person'       => true,
            'gender'       => $record['gender'],
            'labels'       => $labels,
            'descriptions' => $descriptions,
        ]);
    }

    /**
     * Extract gender from configuration.
     *
     * @param Element $object OpenStreetMap element (relation/way/node).
     * @param string[] $warnings
     * @return null|string
     */
    private function getGenderFromConfig($object, array &$warnings = []): ?string
    {
        if (
            $object->type === 'relation' && isset(
                $this->config->gender,
                $this->config->gender->relation,
                $this->config->gender->relation[(string) $object->id]
            )
        ) {
            return $this->config->gender->relation[(string) $object->id];
        } elseif (
            $object->type === 'way' && isset(
                $this->config->gender,
                $this->config->gender->way,
                $this->config->gender->way[(string) $object->id]
            )
        ) {
            return $this->config->gender->way[(string) $object->id];
        }

        return null;
    }

    /**
     * Extact gender from event CSF file (Brussels only).
     *
     * @param Element $object OpenStreetMap element (relation/way/node).
     * @param string[] $warnings
     * @return null|string
     */
    private function getGenderFromEvent($object, array &$warnings = []): ?string
    {
        if (!isset($this->event) || count($this->event) === 0) {
            return null;
        }

        if (isset($object->tags->{'name:fr'}, $this->event[md5($object->tags->{'name:fr'})])) { // @phpstan-ignore-line
            return $this->event[md5($object->tags->{'name:fr'})]; // @phpstan-ignore-line
        } elseif (isset($object->tags->{'name:nl'}, $this->event[md5($object->tags->{'name:nl'})])) { // @phpstan-ignore-line
            return $this->event[md5($object->tags->{'name:nl'})]; // @phpstan-ignore-line
        } elseif (isset($object->tags->{'name'}, $this->event[md5($object->tags->{'name'})])) { // @phpstan-ignore-line
            return $this->event[md5($object->tags->{'name'})]; // @phpstan-ignore-line
        }

        return null;
    }

    /**
     * Create GeoJSON feature "property".
     * Extract gender from Wikidata, or configuration and define source depending.
     *
     * @param Element $object OpenStreetMap element (relation/way/node).
     * @param string[] $warnings
     * @return Properties
     *
     * @throws ErrorException
     */
    private function createProperties($object, array &$warnings = []): Properties
    {
        $properties = new Properties();
        $properties->name = $object->tags->name ?? null; // @phpstan-ignore-line
        $properties->wikidata = $object->tags->wikidata ?? null; // @phpstan-ignore-line
        $properties->source = null;
        $properties->gender = null;
        $properties->details = null;

        // Try to extract information from `name:etymology:wikidata` tag in OpenStreetMap
        if (isset($object->tags->{'name:etymology:wikidata'})) { // @phpstan-ignore-line
            $idsEtymology = explode(';', $object->tags->{'name:etymology:wikidata'}); // @phpstan-ignore-line
            $idsEtymology = array_map('trim', $idsEtymology);

            $detailsEtymology = [];
            foreach ($idsEtymology as $id) {
                $wikiPath = sprintf('%s/wikidata/%s.json', self::OUTPUTDIR, $id);

                $entity = Wikidata::read($wikiPath);
                if ($entity->id !== $id) {
                    $warnings[] = sprintf('Entity "%s" is (probably) redirected to "%s" (tagged as `name:etymology:wikidata` in %s(%s)).', $id, $entity->id, $object->type, $object->id);
                }

                $detailsEtymology[] = $this->extractDetailsFromWikidata($entity, $warnings);
            }

            $_person = array_unique(array_column($detailsEtymology, 'person'));
            $_gender = array_unique(array_column($detailsEtymology, 'gender'));

            $genderEtymology = (count($_person) === 1 && current($_person) === true) ? (count($_gender) === 1 ? current($_gender) : '+') : null;

            if (count($detailsEtymology) === 1) {
                $detailsEtymology = current($detailsEtymology);
            }
        }

        // Try to extract information from `P138` (NamedAfter) property in Wikidata
        if (isset($object->tags->wikidata)) {
            $wikiPath = sprintf('%s/wikidata/%s.json', self::OUTPUTDIR, $object->tags->wikidata);

            $entity = Wikidata::read($wikiPath);
            if ($entity->id !== $object->tags->wikidata) {
                $warnings[] = sprintf('Entity "%s" is (probably) redirected to "%s" (tagged as `wikidata` in %s(%s)).', $object->tags->wikidata, $entity->id, $object->type, $object->id);
            }

            $idsWikidata = Wikidata::extractNamedAfter($entity);

            if (!is_null($idsWikidata)) {
                $detailsWikidata = [];
                foreach ($idsWikidata as $id) {
                    $wikiPath = sprintf('%s/wikidata/%s.json', self::OUTPUTDIR, $id);

                    $entity = Wikidata::read($wikiPath);
                    if ($entity->id !== $id) {
                        $warnings[] = sprintf('Entity "%s" is (probably) redirected to "%s" (set as `P138` property in "%s").', $id, $entity->id, $object->tags->wikidata);
                    }

                    $detailsWikidata[] = $this->extractDetailsFromWikidata($entity, $warnings);
                }

                $_person = array_unique(array_column($detailsWikidata, 'person'));
                $_gender = array_unique(array_column($detailsWikidata, 'gender'));

                $genderWikidata = (count($_person) === 1 && current($_person) === true) ? (count($_gender) === 1 ? current($_gender) : '+') : null;

                if (count($detailsWikidata) === 1) {
                    $detailsWikidata = current($detailsWikidata);
                }
            }
        }

        if (isset($idsEtymology, $idsWikidata) && !is_null($idsWikidata)) {
            sort($idsEtymology);
            sort($idsWikidata);

            if ($idsEtymology !== $idsWikidata) {
                $warnings[] = sprintf(
                    'Mismatch between `name:etymology:wikidata` tag (%s) and `P138` (NamedAfter) property (%s) for %s(%s).',
                    implode(', ', $idsEtymology),
                    implode(', ', $idsWikidata),
                    $object->type,
                    $object->id
                );
            }

            if ($genderEtymology !== $genderWikidata) {
                $warnings[] = sprintf(
                    '<warning>Gender mismatch (%s/%s) between `name:etymology:wikidata` tag (%s) and `P138` (NamedAfter) property (%s) for %s(%s).</warning>',
                    $genderEtymology ?? '-',
                    $genderWikidata ?? '-',
                    implode(', ', $idsEtymology),
                    implode(', ', $idsWikidata),
                    $object->type,
                    $object->id
                );
            }
        }

        if (isset($genderEtymology, $detailsEtymology)) {
            // If `name:etymology:wikidata` tag is set, use it to extract details and determine gender.
            $properties->source = 'wikidata';
            $properties->gender = $genderEtymology;
            $properties->details = $detailsEtymology;
        } elseif (isset($genderWikidata, $detailsWikidata)) {
            // If `P138` (NamedAfter) property is set, use it to extract details and determine gender.
            $properties->source = 'wikidata';
            $properties->gender = $genderWikidata;
            $properties->details = $detailsWikidata;
        } elseif (!is_null($details = $this->extractDetailsFromCSV($object, $warnings))) {
            // If relation/way is defined in CSV file, use it to extract details and determine gender.
            $properties->source = 'csv';
            $properties->gender = $details->gender;
            $properties->details = $details;
        } elseif (!is_null($gender = $this->getGenderFromConfig($object, $warnings))) {
            // If gender for relation/way identifier is set in configuration, use it to determine gender.
            $properties->source = 'config';
            $properties->gender = $gender;
        } elseif (!is_null($gender = $this->getGenderFromEvent($object, $warnings))) {
            // If gender is set in event file, use it to determine gender (Brussels only).
            $properties->source = 'event';
            $properties->gender = $gender;
        }

        return $properties;
    }

    /**
     * Create GeoJSON feature "geometry" based on Overpass query result.
     *
     * @param Way|Relation $object OpenStreetMap element (relation/way).
     * @param Relation[] $relations OpenStreetMap relations.
     * @param Way[] $ways OpenStreetMap ways.
     * @param Node[] $nodes OpenStreetMap nodes.
     * @param string[] $warnings
     *
     * @return null|LineString|MultiLineString
     */
    private static function createGeometry($object, array $relations, array $ways, array $nodes, array &$warnings = [])
    {
        if ($object->type === 'relation') {
            /** @var Relation */ $object = $object;
            $members = array_filter(
                $object->members,
                function ($member): bool {
                    return $member->role === 'street' || $member->role === 'outer';
                }
            );

            if (count($members) === 0) {
                $warnings[] = sprintf('No "street" or "outer" member in relation(%d).</warning>', $object->id);

                return null;
            } else {
                $coordinates = [];
                foreach ($members as $member) {
                    if ($member->type === 'relation') {
                        if (isset($relations[$member->ref])) {
                            $geometry = self::createGeometry($relations[$member->ref], $relations, $ways, $nodes, $warnings);
                            if (!is_null($geometry)) {
                                if ($geometry->type === 'LineString') {
                                    /** @var LineString */ $geometry = $geometry;
                                    $coordinates[] = $geometry->coordinates;
                                } elseif ($geometry->type === 'MultiLineString') {
                                    /** @var MultiLineString */ $geometry = $geometry;
                                    $coordinates = array_merge($coordinates, $geometry->coordinates);
                                }
                            }
                        } else {
                            $warnings[] = sprintf('<warning>Can\'t find relation(%d) in relation(%d).</warning>', $member->ref, $object->id);
                        }
                    } elseif ($member->type === 'way') {
                        if (isset($ways[$member->ref])) {
                            /** @var null|LineString */ $geometry = self::createGeometry($ways[$member->ref], $relations, $ways, $nodes, $warnings);
                            if (!is_null($geometry)) {
                                $coordinates[] = $geometry->coordinates;
                            }
                        } else {
                            $warnings[] = sprintf('<warning>Can\'t find way(%d) in relation(%d).</warning>', $member->ref, $object->id);
                        }
                    }
                }
                if (count($coordinates) === 0) {
                    $warnings[] = sprintf('<warning>No geometry for %s(%d).</warning>', $object->type, $object->id);

                    return null;
                }
                return new MultiLineString($coordinates);
            }
        } elseif ($object->type === 'way') {
            /** @var Way */ $object = $object;

            $coordinates = [];
            foreach ($object->nodes as $id) {
                $node = $nodes[$id] ?? null;

                if (is_null($node)) {
                    $warnings[] = sprintf('<warning>Can\'t find node(%d) in way(%d).</warning>', $id, $object->id);
                } else {
                    $coordinates[] = [$node->lon, $node->lat];
                }
            }
            if (count($coordinates) === 0) {
                $warnings[] = sprintf('<warning>No geometry for %s(%d).</warning>', $object->type, $object->id);

                return null;
            }
            return new LineString($coordinates);
        }

        return null;
    }

    /**
     * Create GeoJSON FeatureCollection.
     *
     * @param string $type OpenStreetMap element type (relation/way).
     * @param Element[] $elements OpenStreetMap elements (relation/way/node).
     * @param OutputInterface $output
     * @return FeatureCollection
     *
     * @throws ErrorException
     */
    private function createGeoJSON(string $type, array $elements, OutputInterface $output): FeatureCollection
    {
        /** @var Node[] */ $nodes = self::extractElements('node', $elements);
        /** @var Way[] */ $ways = self::extractElements('way', $elements);
        /** @var Relation[] */ $relations = self::extractElements('relation', $elements);

        $output->writeln(sprintf('%d node(s), %d way(s), %d relation(s)', count($nodes), count($ways), count($relations)));

        $geojson = new FeatureCollection();

        $objects = $type === 'relation' ? $relations : $ways;

        $warnings = [];
        $progressBar = new ProgressBar($output, count($objects));
        $progressBar->start();

        foreach ($objects as $object) {
            $properties = $this->createProperties($object, $warnings);
            $geometry = self::createGeometry($object, $relations, $ways, $nodes, $warnings);

            $feature = new Feature();
            $feature->id = $object->id;
            $feature->properties = $properties;
            $feature->geometry = $geometry;

            $geojson->features[] = $feature;

            $progressBar->advance();
        }

        $progressBar->finish();

        $output->writeln(['', ...$warnings]);

        return $geojson;
    }
}
