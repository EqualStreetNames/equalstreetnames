<?php

namespace App\Command;

use App\Model\Overpass\Element;
use App\Model\Overpass\Overpass;
use ErrorException;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Download in JSON format Wikidata item(s) defined in `name:etymology:wikidata` tag for each relation/way.
 *
 * @todo Download Wikidata item defined in `wikidata` tag.
 *
 * @package App\Command
 */
class WikidataCommand extends AbstractCommand
{
    /** {@inheritdoc} */
    protected static $defaultName = 'wikidata';

    /** @var string Wikidata item URL. */
    protected const URL = 'https://www.wikidata.org/wiki/Special:EntityData/';

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

        $this->setDescription('Download data from Wikidata.');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            parent::execute($input, $output);

            $relationPath = sprintf('%s/overpass/%s', self::OUTPUTDIR, OverpassCommand::FILENAME_RELATION);
            if (!file_exists($relationPath) || !is_readable($relationPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "overpass" command first.', $relationPath));
            }
            $wayPath = sprintf('%s/overpass/%s', self::OUTPUTDIR, OverpassCommand::FILENAME_WAY);
            if (!file_exists($wayPath) || !is_readable($wayPath)) {
                throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable. You maybe need to run "overpass" command first.', $wayPath));
            }

            $contentR = file_get_contents($relationPath);
            /** @var Overpass|null */ $overpassR = $contentR !== false ? json_decode($contentR) : null;
            $contentW = file_get_contents($wayPath);
            /** @var Overpass|null */ $overpassW = $contentW !== false ? json_decode($contentW) : null;

            // Only keep ways/relations that have a `wikidata` tag and/or a `name:etymology:wikidata` tag
            $elements = array_filter(
                array_merge($overpassR->elements ?? [], $overpassW->elements ?? []),
                function ($element): bool {
                    return isset($element->tags) &&
                        (isset($element->tags->wikidata) || isset($element->tags->{'name:etymology:wikidata'})); // @phpstan-ignore-line
                }
            );

            // Check count of elements with Wikidata information.
            if (count($elements) === 0) {
                throw new ErrorException('No element with Wikidata information!');
            }

            // Create wikidata directory to store results.
            $outputDir = sprintf('%s/wikidata', self::OUTPUTDIR);
            if (!file_exists($outputDir) || !is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $warnings = [];
            $progressBar = new ProgressBar($output, count($elements));
            $progressBar->start();

            foreach ($elements as $element) {
                $wikidataTag = $element->tags->wikidata ?? null; // @phpstan-ignore-line
                $etymologyTag = $element->tags->{'name:etymology:wikidata'} ?? null; // @phpstan-ignore-line

                // Download Wikidata item(s) defined in `name:etymology:wikidata` tag
                if (!is_null($etymologyTag)) {
                    $identifiers = explode(';', $etymologyTag);
                    $identifiers = array_map('trim', $identifiers);

                    foreach ($identifiers as $identifier) {
                        // Check that the value of the tag is a valid Wikidata item identifier
                        if (preg_match('/^Q.+$/', $identifier) !== 1) {
                            throw new Exception(sprintf('Format of `name:etymology:wikidata` is invalid (%s) for %s(%d).', $identifier, $element->type, $element->id));
                        }

                        // Download Wikidata item
                        $path = sprintf('%s/%s.json', $outputDir, $identifier);
                        if (!file_exists($path)) {
                            self::save($identifier, $element, $path, $warnings);
                        }
                    }
                }

                // Download Wikidata item defined in `wikidata` tag
                // if (!is_null($wikidataTag)) {
                //     $path = sprintf('%s/%s.json', $outputDir, $wikidataTag);
                //     if (!file_exists($path)) {
                //       self::save($wikidataTag, $element, $path, $warnings);
                //     }
                // }

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

    /**
     * Send request and store result.
     * Display warning if the Wikidata item doesn't exist or if the process can't download the Wikidate item.
     *
     * @param string $identifier Wikidata item identifier.
     * @param Element $element OpenStreetMap element (relation/way/node).
     * @param string $path Path where to store the result.
     * @param string[] $warnings
     * @return void
     *
     * @throws GuzzleException
     */
    private static function save(string $identifier, $element, string $path, array &$warnings = []): void
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
