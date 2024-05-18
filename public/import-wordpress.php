<?php

require_once "../vendor/autoload.php";

use Pagenode\Importer\Wordpress\Importer;
use Symfony\Component\Yaml\Yaml;

$settings = Yaml::parseFile(Importer::CONFIG_FILE);

$importer = new Importer($settings);
$importer
    ->fetch()
    ->transformContent()
    ->copyAssets()
    ->generatePagenodeMarkdownFiles();

dump('DONE !');
