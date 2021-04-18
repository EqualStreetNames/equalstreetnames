<?php

namespace App\Command;

use ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

abstract class AbstractCommand extends Command
{
  protected $city;
  protected string $cityDir;
  protected string $cityOutputDir;
  protected array $config = [];
  protected string $processOutputDir = 'data';

  protected function configure()
  {
    $this->addOption('city', 'c', InputOption::VALUE_REQUIRED, 'City directory: <my-country>/<my-city>');
  }

  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red'));

    $this->city = $input->getOption('city');
  }

  protected function interact(InputInterface $input, OutputInterface $output)
  {
    if (is_null($this->city)) {
      $helper = $this->getHelper('question');

      $cities = array_map(function($path) {
        return substr($path, 10);
      }, glob('../cities/*/*'));

      $question = new ChoiceQuestion('Choose a city: ', $cities);
      $question->setAutocompleterValues($cities);

      $this->city = $helper->ask($input, $output, $question);
    }

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

    $output->writeln([
      sprintf('<info>%s</info>', $this->getDescription()),
      sprintf('<comment>City: %s</comment>', $this->city),
    ]);

    $configPath = sprintf('%s/config.php', $this->cityDir);
    if (!file_exists($configPath) || !is_readable($configPath)) {
      throw new ErrorException(sprintf('File "%s" doesn\'t exist or is not readable.', $configPath));
    }

    $this->config = require $configPath;
  }
}
