#!/usr/bin/env php
<?php


$_SERVER["DOCUMENT_ROOT"] = __DIR__;


include("vendor/autoload.php");
include("ExampleCommand.php");


$command = new ExampleCommand();
$status = $command->run();

exit($status);