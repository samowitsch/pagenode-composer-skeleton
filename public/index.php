<?php
setlocale(LC_ALL, ['de_DE.UTF-8', 'de_DE.utf8']);

define('PN_DATE_FORMAT', 'd.m.Y H:i:s');

require_once('../vendor/autoload.php');
require_once '../src/Pagenode/pagenode.php';

route('/', function () {
    include('templates/node.html.php');
});

// first level navigation
route('/{keyword}', function ($keyword) {
    $posts = select('nodes/blog/')->newest(300);

    $node = select('nodes/')->one(['keyword' => $keyword]);
    if (!$node) {
        return false;
    }

    include('templates/node.html.php');
});


// blog routing
route('/{year}/{month}/{day}/{keyword}', function ($year, $month, $day, $keyword) {
    // $path = sprintf('nodes/blog/%s/%s/%s/', $year, $month, $day);
    
    $keyword = sprintf('%s-%s-%s-%s', $year, $month, $day, $keyword);
    
    $path = sprintf('nodes/blog/');
    $node = select($path)->one(['keyword' => $keyword]);
    if (!$node) {
        return false;
    }

    include('templates/detail.html.php');
});


// 404 handling
route('/*', function () {
    $node = select('nodes/')->one(['keyword' => '404']);
    include('templates/node.html.php');
});

// Home
reroute('/', '/home');

dispatch();
