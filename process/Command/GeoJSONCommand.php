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

    /** @var string Filename for the relations GeoJSON file. */
    public const FILENAME_RELATION = 'relations.geojson';
    /** @var string Filename for the ways GeoJSON file. */
    public const FILENAME_WAY = 'ways.geojson';

    protected array $csv;

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
                $csvPath = sprintf('%s/event-2020-02-17/gender.csv', $this->cityDir);
                if (file_exists($csvPath) && is_readable($csvPath)) {
                    $this->csv = [];
                    $handle = fopen(sprintf('%s/event-2020-02-17/gender.csv', $this->cityDir), 'r');
                    if ($handle !== false) {
                        while (($data = fgetcsv($handle)) !== false) {
                            $streetFR = $data[0];
                            $streetNL = $data[1];
                            $gender = $data[2];

                            if (isset($this->csv[md5($streetFR)]) && $this->csv[md5($streetFR)] !== $gender) {
                                throw new ErrorException('');
                            }
                            if (isset($this->csv[md5($streetNL)]) && $this->csv[md5($streetNL)] !== $gender) {
                                throw new ErrorException('');
                            }

                            $this->csv[md5($streetFR)] = $gender;
                            $this->csv[md5($streetNL)] = $gender;
                        }
                        fclose($handle);
                    }
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
     * @param Config $config Configuration (from `config.php`).
     * @param string[] $warnings
     * @return Details
     */
    private static function extractDetails($entity, Config $config, array &$warnings = []): Details
    {
        $dateOfBirth = Wikidata::extractDateOfBirth($entity);
        $dateOfDeath = Wikidata::extractDateOfDeath($entity);

        $person = Wikidata::isPerson($entity, $config->instances);
        if (is_null($person)) {
            $warnings[] = sprintf('No instance or subclass for "%s".', $entity->id);
            $person = false;
        }

        return new Details([
            'wikidata'     => $entity->id,
            'person'       => $person,
            'gender'       => Wikidata::extractGender($entity),
            'labels'       => Wikidata::extractLabels($entity, $config->languages),
            'descriptions' => Wikidata::extractDescriptions($entity, $config->languages),
            'nicknames'    => Wikidata::extractNicknames($entity, $config->languages),
            'birth'        => is_null($dateOfBirth) ? null : intval(substr($dateOfBirth, 0, 5)),
            'death'        => is_null($dateOfDeath) ? null : intval(substr($dateOfDeath, 0, 5)),
            'sitelinks'    => Wikidata::extractSitelinks($entity, $config->languages),
            'image'        => Wikidata::extractImage($entity),
        ]);
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

        if (isset($object->tags->{'name:etymology:wikidata'})) { // @phpstan-ignore-line
            // If `name:etymology:wikidata` tag is set, use it to determine gender.
            $identifiers = explode(';', $object->tags->{'name:etymology:wikidata'}); // @phpstan-ignore-line
            $identifiers = array_map('trim', $identifiers);

            $details = [];
            foreach ($identifiers as $identifier) {
                $wikiPath = sprintf('%s/wikidata/%s.json', self::OUTPUTDIR, $identifier);
                if (!file_exists($wikiPath) || !is_readable($wikiPath)) {
                    $warnings[] = sprintf('<warning>File "%s" doesn\'t exist or is not readable (tagged in %s(%s)). You maybe need to run "wikidata" command first.</warning>', $wikiPath, $object->type, $object->id);
                } else {
                    $content = file_get_contents($wikiPath);
                    $json = $content !== false ? json_decode($content) : null;
                    if (is_null($json)) {
                        throw new ErrorException(sprintf('Can\'t read "%s".', $wikiPath));
                    }
                    $entity = current($json->entities);

                    if ($entity->id !== $identifier) {
                        $warnings[] = sprintf('Entity "%s" is (probably) redirected to "%s" (tagged in %s(%s)).', $identifier, $entity->id, $object->type, $object->id);
                    }

                    $details[] = self::extractDetails($entity, $this->config, $warnings);
                }
            }

            $_person = array_unique(array_column($details, 'person'));
            $_gender = array_unique(array_column($details, 'gender'));

            $gender = (count($_person) === 1 && current($_person) === true) ? (count($_gender) === 1 ? current($_gender) : '+') : null;

            if (count($details) === 1) {
                $details = current($details);
            }

            $properties->source = 'wikidata';
            $properties->gender = $gender;
            $properties->details = $details;
        } elseif (
            $object->type === 'relation' && isset(
                $this->config->gender,
                $this->config->gender->relation,
                $this->config->gender->relation[(string) $object->id]
            )
        ) {
            // If gender for relation identifier is set in configuration, use it to determine gender.
            $properties->source = 'config';
            $properties->gender = $this->config->gender->relation[(string) $object->id];
        } elseif (
            $object->type === 'way' && isset(
                $this->config->gender,
                $this->config->gender->way,
                $this->config->gender->way[(string) $object->id]
            )
        ) {
            // If gender for way identifier is set in configuration, use it to determine gender.
            $properties->source = 'config';
            $properties->gender = $this->config->gender->way[(string) $object->id];
        } elseif (isset($this->csv) && count($this->csv) > 0) {
            // If gender is set in event file, use it to determine gender (Brussels only).
            if (isset($object->tags->{'name:fr'}, $this->csv[md5($object->tags->{'name:fr'})])) { // @phpstan-ignore-line
                $properties->source = 'event';
                $properties->gender = $this->csv[md5($object->tags->{'name:fr'})]; // @phpstan-ignore-line
            } elseif (isset($object->tags->{'name:nl'}, $this->csv[md5($object->tags->{'name:nl'})])) { // @phpstan-ignore-line
                $properties->source = 'event';
                $properties->gender = $this->csv[md5($object->tags->{'name:nl'})]; // @phpstan-ignore-line
            } elseif (isset($object->tags->{'name'}, $this->csv[md5($object->tags->{'name'})])) { // @phpstan-ignore-line
                $properties->source = 'event';
                $properties->gender = $this->csv[md5($object->tags->{'name'})]; // @phpstan-ignore-line
            }
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
