<?php
setlocale(LC_ALL, ['de_DE.UTF-8', 'de_DE.utf8']);

require_once('../vendor/autoload.php');
require_once '../src/pagenode/pagenode.php';

route('/', function () {
    include('templates/node.html.php');
});

// first level navigation
route('/{keyword}', function ($keyword) {
    $node = select('nodes/')->one(['keyword' => $keyword]);
    if (!$node) {
        return false;
    }

    include('templates/node.html.php');
});

// 404 handling
route('/*', function () {
    $node = select('nodes/')->one(['keyword' => '404']);
    include('templates/node.html.php');
});

// Home
reroute('/', '/home');

dispatch();
