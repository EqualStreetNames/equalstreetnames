<?php

namespace App\Command;

use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatisticsCommand extends AbstractCommand
{
  protected static $defaultName = 'statistics';

  protected string $relationPath;
  protected string $wayPath;

  protected function configure()
  {
    parent::configure();

    $this->setDescription('Calculate statistics.');
  }

  protected function interact(InputInterface $input, OutputInterface $output)
  {
    parent::interact($input, $output);

    $this->relationPath = sprintf('%s/relations.geojson', $this->cityOutputDir);
    $this->wayPath = sprintf('%s/ways.geojson', $this->cityOutputDir);
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    try {
      parent::execute($input, $output);

      if (!file_exists($this->relationPath) || !is_readable($this->relationPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "geojson" command first.', $this->relationPath));
      }
      if (!file_exists($this->wayPath) || !is_readable($this->wayPath)) {
        throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "geojson" command first.', $this->relationPath));
      }

      $streets = [];

      $relations = json_decode(file_get_contents($this->relationPath));
      $ways = json_decode(file_get_contents($this->wayPath));

      // Extract necesarry data
      foreach ($relations->features as $feature) {
        $street = self::extract($feature);
        $street['type'] = 'relation';

        $streets[] = $street;
      }
      foreach ($ways->features as $feature) {
        $street = self::extract($feature);
        $street['type'] = 'way';

        $streets[] = $street;
      }

      // Group by streetname
      $streets = self::groupBy($streets, $output);

      // Sort streets
      $streets = self::sort($streets);

      // Export to CSV ("gender.csv" + "other.csv")
      self::exportCSV(
        sprintf('%s/gender.csv', $this->cityOutputDir),
        array_filter($streets, function ($street) {
          return !is_null($street['gender']);
        })
      );
      self::exportCSV(
        sprintf('%s/other.csv', $this->cityOutputDir),
        array_filter($streets, function ($street) {
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

      foreach ($streets as $street) {
        $genders[$street['gender'] ?? '-']++;
      }

      // Store statistics
      file_put_contents(sprintf('%s/statistics.json', $this->cityOutputDir), json_encode($genders));

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

      return Command::SUCCESS;
    } catch (Exception $error) {
      $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

      return Command::FAILURE;
    }
  }

  private static function extract($feature): array
  {
    if (!is_null($feature->properties->details) && is_array($feature->properties->details)) {
      $wikidata = implode(';', array_column($feature->properties->details, 'wikidata'));
    } else {
      $wikidata = $feature->properties->details->wikidata ?? null;
    }

    return [
      'id'       => $feature->id,
      'name'     => $feature->properties->name,
      'gender'   => $feature->properties->gender ?? null,
      'wikidata' => $wikidata,
    ];
  }

  private static function normalize(string $string, string $charset = 'utf-8'): string
  {
    $str = htmlentities($string, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    $str = preg_replace('#&[^;]+;#', '', $str);

    // $str = strtolower($str);

    return $str;
  }

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
    foreach ($groups as $streets) {
      if (count($streets) === 1) {
        $results[] = [
          'name'     => $streets[0]['name'],
          'gender'   => $streets[0]['gender'],
          'wikidata' => $streets[0]['wikidata'],
          'type'     => $streets[0]['type'],
        ];
      } else {
        $types = array_unique(array_column($streets, 'type'));
        $genders = array_values(array_filter(array_unique(array_column($streets, 'gender')), function ($type) { return !is_null($type); }));
        $wikidatas = array_values(array_filter(array_unique(array_column($streets, 'wikidata')), function ($type) { return !is_null($type); }));

        sort($types);
        sort($genders);
        sort($wikidatas);

        if (count($genders) > 1) {
          $output->writeln(sprintf('<warning>Gender mismatch (%s) for street "%s".</warning>',  implode(', ', $genders), $streets[0]['name']));
        }
        if (count($wikidatas) > 1) {
          $output->writeln(sprintf('<warning>Wikidata mismatch (%s) for street "%s".</warning>',  implode(', ', $wikidatas), $streets[0]['name']));
        }

        $results[] = [
          'name'     => $streets[0]['name'],
          'gender'   => count($genders) === 0 ? null : (count($genders) > 1 ? '?' : $genders[0]),
          'wikidata' => count($wikidatas) === 0 ? null : implode(';', $wikidatas),
          'type'     => count($types) > 1 ? implode('+', $types) : $types[0],
        ];
      }
    }

    return $results;
  }

  private static function exportCSV(string $path, array $streets): void
  {
    $fp = fopen($path, 'w');

    fputcsv($fp, ['name', 'gender', 'wikidata', 'type']);

    foreach ($streets as $street) {
      fputcsv($fp, $street);
    }

    fclose($fp);
  }
}
