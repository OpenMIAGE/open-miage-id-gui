<?php

require_once 'config.php';
require_once 'src.php';
require_once 'lib.php';

Import::php("OpenM-ID.gui.OpenM_ID_APIServer");
$server = new OpenM_ID_APIServer();
$server->handle();
?>