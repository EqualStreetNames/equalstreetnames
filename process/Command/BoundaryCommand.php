<?php

namespace App\Command;

use ErrorException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BoundaryCommand extends AbstractCommand
{
    protected static $defaultName = 'boundary';

    protected const URL = 'http://polygons.openstreetmap.fr/get_geojson.py';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Download city boundary from OpenStreetMap.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            if (!isset($this->config['relationId']) || !is_int($this->config['relationId'])) {
                throw new ErrorException('"relationId" parameter is missing or is invalid in "config.php".');
            }

            $boundary = self::query($this->config['relationId']);

            file_put_contents(sprintf('%s/boundary.geojson', $this->cityOutputDir), $boundary);

            return Command::SUCCESS;
        } catch (Exception $error) {
            $output->writeln(sprintf('<error>%s</error>', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    private static function query(int $id): string
    {
        $url = sprintf('%s?id=%d', self::URL, $id);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);

        $status = $response->getStatusCode();

        if ($status !== 200) {
            throw new ErrorException($response->getReasonPhrase());
        }

        return (string) $response->getBody();
    }
}
