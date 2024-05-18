<?php

// src/Command/CreateUserCommand.php
namespace Pagenode\Importer\Command;

use Pagenode\Importer\Wordpress\Importer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'import:wordpress',
    description: 'Import wordpress data'
)]
class ImportWordpressCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $settings = Yaml::parseFile(__DIR__ . '/../../../../config/import-wordpress-config.yaml');
        $importer = new Importer($settings);
        $importer
            ->fetch()
            ->transformContent()
            ->copyAssets();

        $io->note(sprintf(
            '%s posts found',
            count($importer->getPosts())
        ));

        $io->note(sprintf(
            '%s posts with content blocks',
            count($importer->getPostsWithContentBlocks())
        ));

        $io->note(sprintf(
            '%s posts with sliders',
            count($importer->getPostsWithSlider())
        ));

        $io->note(sprintf(
            '%s posts with pre tags',
            count($importer->getPostsWithPreTags())
        ));

        $importer->generatePagenodeMarkdownFiles();

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
