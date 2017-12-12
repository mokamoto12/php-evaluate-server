<?php

require_once(__DIR__ . '/../vendor/autoload.php');

$output = new Symfony\Component\Console\Output\StreamOutput(fopen('php://output', 'w'));
$app = new Mokamoto12\Evaluate\Application($output);
$app->run($_REQUEST);
