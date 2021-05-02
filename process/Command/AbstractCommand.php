<?php

namespace App\Command;

use App\Model\Config\Config;
use ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

abstract class AbstractCommand extends Command
{
    protected ?string $city;
    protected string $cityDir;
    protected string $cityOutputDir;
    protected Config $config;
    protected array $csv = [];
    protected string $processOutputDir = 'data';

    protected function configure(): void
    {
        $this->addOption('city', 'c', InputOption::VALUE_REQUIRED, 'City directory: <my-country>/<my-city>');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red'));
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $city = $input->getOption('city');

        if (is_null($city) || !is_string($city)) {
            $helper = $this->getHelper('question');

            $glob = glob('../cities/*/*');
            if ($glob !== false) {
                $cities = array_map(function ($path): string {
                    return substr($path, 10);
                }, $glob);

                $question = new ChoiceQuestion('Choose a city: ', $cities);
                $question->setAutocompleterValues($cities);

                $this->city = $helper->ask($input, $output, $question);
            }
        } else {
            $this->city = $city;
        }

        $output->writeln([
            sprintf('<info>%s</info>', $this->getDescription()),
            sprintf('<comment>City: %s</comment>', $this->city),
        ]);

        $this->cityDir = sprintf('../cities/%s', $this->city);
        $this->cityOutputDir = sprintf('%s/data', $this->cityDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->cityDir) || !is_dir($this->cityDir)) {
            throw new ErrorException(sprintf('Directory "%s" doesn\'t exist.', $this->cityDir));
        }

        if (!file_exists($this->cityOutputDir) || !is_dir($this->cityOutputDir)) {
            mkdir($this->cityOutputDir, 0777, true);
        }
        if (!file_exists($this->processOutputDir) || !is_dir($this->processOutputDir)) {
            mkdir($this->processOutputDir, 0777, true);
        }

        $configPath = sprintf('%s/config.php', $this->cityDir);
        if (!file_exists($configPath) || !is_readable($configPath)) {
            throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $configPath));
        }

        $configArray = require $configPath;
        $this->config = new Config($configArray);
    }
}
