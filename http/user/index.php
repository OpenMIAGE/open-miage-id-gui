<?php

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/src.php';
require_once dirname(__DIR__) . '/lib.php';

Import::addClassPath();
Import::php("OpenM-Controller.gui.OpenM_ViewDefaultServer");
$server = new OpenM_ViewDefaultServer();
$server->handle();
?>