<?php

use Kedomingo\DtdParser\DTDParser;

require_once 'vendor/autoload.php';

$builder = new \DI\ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAnnotations(false);

$container = $builder->build();

$parser = $container->get(DTDParser::class);

$parser->parse(file_get_contents(__DIR__.'/propertyList.dtd'));

