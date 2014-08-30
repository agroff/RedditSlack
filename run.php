#!/usr/bin/env php
<?php


$_SERVER["DOCUMENT_ROOT"] = __DIR__;


include("vendor/autoload.php");
include("PostNewPosts.php");
include("config.php");


$command = new PostNewPosts();
$command->setConfig($config);
$status = $command->run();

exit($status);