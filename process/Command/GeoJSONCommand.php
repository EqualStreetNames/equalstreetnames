<?php

namespace App\Command;

use App\Wikidata\Wikidata;
use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeoJSONCommand extends AbstractCommand
{
  protected static $defaultName = 'geojson';

  protected function configure()
  {
    parent::configure();

    $this->setDescription('Generate GeoJSON files.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    try {
      parent::execute($input, $output);

      $relationPath = sprintf('%s/overpass/relation.json', $this->processOutputDir);
      if (!file_exists($relationPath) || !is_readable($relationPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "overpass" command first.', $relationPath));
      }
      $wayPath = sprintf('%s/overpass/way.json', $this->processOutputDir);
      if (!file_exists($wayPath) || !is_readable($wayPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "overpass" command first.', $wayPath));
      }

      $overpassR = json_decode(file_get_contents($relationPath));
      $overpassW = json_decode(file_get_contents($wayPath));

      $output->write('Relations: ');
      $geojsonR = $this->createGeoJSON('relation', $overpassR->elements ?? [], $output);
      $output->write('Ways: ');
      $geojsonW = $this->createGeoJSON('way', $overpassW->elements ?? [], $output);

      if (isset($this->config['exclude'], $this->config['exclude']['relation']) && is_array($this->config['exclude']['relation'])) {
        $geojsonR['features'] = array_filter($geojsonR['features'], function ($feature) {
          return !in_array($feature['id'], $this->config['exclude']['relation']);
        });
      }
      if (isset($this->config['exclude'], $this->config['exclude']['ways']) && is_array($this->config['exclude']['ways'])) {
        $geojsonW['features'] = array_filter($geojsonW['features'], function ($feature) {
          return !in_array($feature['id'], $this->config['exclude']['ways']);
        });
      }

      file_put_contents(
        sprintf('%s/relations.geojson', $this->cityOutputDir),
        json_encode($geojsonR)
      );
      file_put_contents(
        sprintf('%s/ways.geojson', $this->cityOutputDir),
        json_encode($geojsonW)
      );

      return Command::SUCCESS;
    } catch (Exception $error) {
      $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

      return Command::FAILURE;
    }
  }

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

  private static function extractDetails($entity, array $config, array &$warnings = []): ?array
  {
    $dateOfBirth = Wikidata::extractDateOfBirth($entity);
    $dateOfDeath = Wikidata::extractDateOfDeath($entity);

    $person = Wikidata::isPerson($entity, $config['instances']);
    if (is_null($person)) {
      $warnings[] = sprintf('No instance or subclass for "%s".', $entity->id);
      $person = false;
    }

    return [
      'wikidata'     => $entity->id,
      'person'       => $person,
      'labels'       => Wikidata::extractLabels($entity, $config['languages']),
      'descriptions' => Wikidata::extractDescriptions($entity, $config['languages']),
      'nicknames'    => Wikidata::extractNicknames($entity, $config['languages']),
      'birth'        => is_null($dateOfBirth) ? null : intval(substr($dateOfBirth, 0, 5)),
      'death'        => is_null($dateOfDeath) ? null : intval(substr($dateOfDeath, 0, 5)),
      'sitelinks'    => Wikidata::extractSitelinks($entity, $config['languages']),
      'image'        => Wikidata::extractImage($entity),
    ];
  }

  private function createProperties($object, array &$warnings = []): array
  {
    $properties = [
      'name'     => $object->tags->name ?? null,
      'wikidata' => $object->tags->wikidata ?? null,
      'source'   => null,
      'gender'   => null,
      'details'  => null,
    ];

    if (isset($object->tags->{'name:etymology:wikidata'})) {
      $identifiers = explode(';', $object->tags->{'name:etymology:wikidata'});
      $identifiers = array_map('trim', $identifiers);

      $gender = null;
      $details = [];
      foreach ($identifiers as $identifier) {
        $wikiPath = sprintf('%s/wikidata/%s.json', $this->processOutputDir, $identifier);
        if (!file_exists($wikiPath) || !is_readable($wikiPath)) {
          $warnings[] = sprintf('<warning>File "%s" doesn\'t exist or is not readable (tagged in %s(%s)). You maybe need to run "wikidata" command first.</warning>', $wikiPath, $object->type, $object->id);
        } else {
          $json = json_decode(file_get_contents($wikiPath));
          if (is_null($json)) {
            throw new ErrorException(sprintf('Can\'t read "%s".', $wikiPath));
          }
          $entity = current($json->entities);

          if ($entity->id !== $identifier) {
            $warnings[] = sprintf('Entity "%s" is (probably) redirected to "%s".', $identifier, $entity->id);
          }

          $gender = Wikidata::extractGender($entity);
          $details[] = self::extractDetails($entity, $this->config ?? [], $warnings);
        }
      }

      if (count($details) === 1) {
        $details = current($details);
      }

      $properties['source'] = 'wikidata';
      $properties['gender'] = $gender;
      $properties['details'] = $details;
    } else if (isset(
      $this->config['gender'],
      $this->config['gender'][$object->type],
      $this->config['gender'][$object->type][(string) $object->id]
    )) {
      $properties['source'] = 'config';
      $properties['gender'] = $this->config['gender'][$object->type][(string) $object->id];
    }

    return $properties;
  }

  private static function createGeometry($object, array $relations, array $ways, array $nodes, array &$warnings = []): ?array
  {
    $linestrings = [];

    if ($object->type === 'relation') {
      $members = array_filter(
        $object->members,
        function ($member): bool {
          return $member->role === 'street' || $member->role === 'outer';
        }
      );

      if (count($members) === 0) {
        $warnings[] = sprintf('No "street" or "outer" member in relation(%d).</warning>', $object->id);
      } else {
        foreach ($members as $member) {
          if ($member->type === 'relation') {
            if (isset($relations[$member->ref])) {
              $linestrings[] = self::createGeometry($relations[$member->ref], $relations, $ways, $nodes, $warnings);
            } else {
              $linestrings[] = sprintf('<warning>Can\'t find relation(%d) in relation(%d).</warning>', $member->ref, $object->id);
            }
          } else if ($member->type === 'way') {
            if (isset($ways[$member->ref])) {
              $linestrings[] = self::createGeometry($ways[$member->ref], $relations, $ways, $nodes, $warnings);
            } else {
              $linestrings[] = sprintf('<warning>Can\'t find way(%d) in relation(%d).</warning>', $member->ref, $object->id);
            }
          }
        }
      }
    } else if ($object->type === 'way') {
      foreach ($object->nodes as $id) {
        $node = $nodes[$id] ?? null;

        if (is_null($node)) {
          $warnings[] = sprintf('<warning>Can\'t find node(%d) in way(%d).</warning>', $id, $object->id);
        } else {
          $linestrings[] = [$node->lon, $node->lat];
        }
      }
    }

    if (count($linestrings) === 0) {
      $warnings[] = sprintf('<warning>No geometry for %s(%d).</warning>', $object->type, $object->id);

      return null;
    } else if (count($linestrings) > 1) {
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

  private function createGeoJSON(string $type, array $elements, OutputInterface $output)
  {
    $nodes = self::extractElements('node', $elements);
    $ways = self::extractElements('way', $elements);
    $relations = self::extractElements('relation', $elements);

    $output->writeln(sprintf('%d node(s), %d way(s), %d relation(s)', count($nodes), count($ways), count($relations)));

    $geojson = [
      'type'     => 'FeatureCollection',
      'features' => [],
    ];

    $objects = $type === 'relation' ? $relations : $ways;

    $warnings = [];
    $progressBar = new ProgressBar($output, count($objects));
    $progressBar->start();

    foreach ($objects as $object) {
      $properties = $this->createProperties($object, $warnings);
      $geometry = self::createGeometry($object, $relations, $ways, $nodes, $warnings);

      $geojson['features'][] = [
        'type' => 'Feature',
        'id'   => $object->id,
        'properties' => $properties,
        'geometry' => $geometry,
      ];

      $progressBar->advance();
    }

    $progressBar->finish();

    $output->writeln(['', ...$warnings]);

    return $geojson;
  }
}
