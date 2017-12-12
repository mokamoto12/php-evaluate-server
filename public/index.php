<?php

require_once(__DIR__ . '/../vendor/autoload.php');

@$psysh = new Psy\Shell();
$output = new Symfony\Component\Console\Output\StreamOutput(fopen('php://output', 'w'));
$app = new Mokamoto12\Evaluate\Application($psysh, $output);
$app->run($_REQUEST);
