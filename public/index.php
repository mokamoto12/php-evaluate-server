<?php

if (!isset($_REQUEST['eval'])) {
    return;
}

require_once(__DIR__ . '/../vendor/autoload.php');

use Psy\Shell;
use Symfony\Component\Console\Output\StreamOutput;
use Mokamoto12\Evaluate\Application;

@$psysh = new Shell();
$output = new StreamOutput(fopen('php://output', 'w'));
$app = new Application($psysh, $output);
$app->eval($_REQUEST['eval']);
