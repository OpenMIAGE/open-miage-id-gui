<?php

require_once 'config.php';
require_once 'src.php';
require_once 'lib.php';

Import::php("OpenM-Controller.api.OpenM_RESTDefaultServer");
Import::php("OpenM-ID.api.OpenM_ID");

if (isset($_GET["api"]) || OpenM_RESTDefaultServer::containsHelpKeyWork(array_keys($_GET))) {
    OpenM_RESTDefaultServer::handle(array("OpenM_ID"));
} else if (isset($_GET[OpenM_ID::GetOpenID_API])) {
    Import::php("OpenM-ID.api.Impl.OpenM_ID_Account");
    OpenM_ID_Account::getOpenID();
} else if (isset($_GET[OpenM_ID::URI_API])) {
    Import::php("OpenM-ID.api.Impl.OpenM_ID_Account");
    OpenM_ID_Account::uriDisplay();
} else {
    Import::php("OpenM-ID.api.Impl.OpenM_OpenID");
    OpenM_OpenID::handle();
}
?>