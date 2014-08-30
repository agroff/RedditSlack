#!/usr/bin/env php
<?php


$_SERVER["DOCUMENT_ROOT"] = __DIR__;


include("vendor/autoload.php");
include("PostNewPosts.php");


$command = new PostNewPosts();
$status = $command->run();

exit($status);