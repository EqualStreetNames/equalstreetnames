<?php

namespace App\Command;

use ErrorException;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WikidataCommand extends AbstractCommand
{
    protected static $defaultName = 'wikidata';

    protected const URL = 'https://www.wikidata.org/wiki/Special:EntityData/';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Download data from Wikidata.');
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

            $relations = json_decode(file_get_contents($relationPath));
            $ways = json_decode(file_get_contents($wayPath));

          // Only keep ways/relations that have a `wikidata` tag and/or a `name:etymology:wikidata` tag
            $elements = array_filter(
                array_merge($relations->elements ?? [], $ways->elements ?? []),
                function ($element): bool {
                    return isset($element->tags) &&
                    (isset($element->tags->wikidata) || isset($element->tags->{'name:etymology:wikidata'}));
                }
            );

            $outputDir = sprintf('%s/wikidata', $this->processOutputDir);
            if (!file_exists($outputDir) || !is_dir($outputDir)) {
                  mkdir($outputDir, 0777, true);
            }

            $warnings = [];
            $progressBar = new ProgressBar($output, count($elements));
            $progressBar->start();

          // $progressBar->setRedrawFrequency(5);
          // $progressBar->maxSecondsBetweenRedraws(5);
          // $progressBar->minSecondsBetweenRedraws(1);

            foreach ($elements as $element) {
                $wikidataTag = $element->tags->wikidata ?? null;
                $etymologyTag = $element->tags->{'name:etymology:wikidata'} ?? null;

                if (!is_null($etymologyTag)) {
                    $identifiers = explode(';', $etymologyTag);
                    $identifiers = array_map('trim', $identifiers);

                    foreach ($identifiers as $identifier) {
                        // Check that the value of the tag is a valid Wikidata item identifier
                        if (preg_match('/^Q.+$/', $identifier) !== 1) {
                            throw new Exception(sprintf('Format of `name:etymology:wikidata` is invalid (%s) for %s(%d).%s', $identifier, $element['type'], $element['id']));
                        }

                        // Download Wikidata item
                        $path = sprintf('%s/%s.json', $outputDir, $identifier);
                        if (!file_exists($path)) {
                            self::save($identifier, $element, $path, $warnings);
                        }
                    }
                }

                if (!is_null($wikidataTag)) {
                  // Download Wikidata item
                  // $path = sprintf('%s/%s.json', $outputDir, $wikidataTag);
                  // if (!file_exists($path)) {
                  //   self::save($wikidataTag, $element, $path, $warnings);
                  // }
                }


                $progressBar->advance();
            }

            $progressBar->finish();

            $output->writeln(['', ...$warnings]);

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    private static function save(string $identifier, $element, string $path, &$warnings = []): void
    {
        $url = sprintf('%s%s.json', self::URL, $identifier);

        try {
            $client = new \GuzzleHttp\Client();
            $client->request('GET', $url, ['sink' => $path]);
        } catch (BadResponseException $exception) {
            if (file_exists($path)) {
                unlink($path);
            }

            switch ($exception->getResponse()->getStatusCode()) {
                case 404:
                    $warnings[] = sprintf('<warning>Wikidata item %s for %s(%d) does not exist.</warning>', $identifier, $element->type, $element->id);
                    break;
                default:
                    $warnings[] = sprintf('<warning>Error while fetching Wikidata item %s for %s(%d): %s.</warning>', $identifier, $element->type, $element->id, $exception->getMessage());
                    break;
            }
        }
    }
}
