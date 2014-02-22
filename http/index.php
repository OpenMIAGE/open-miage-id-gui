<?php

require_once 'config.php';
require_once 'src.php';
require_once 'lib.php';

Import::php("OpenM-Controller.api.OpenM_RESTDefaultServer");
Import::php("OpenM-ID.api.OpenM_ID");
Import::php("OpenM-ID.api.Impl.OpenM_OpenID");

if (isset($_GET["api"]) || OpenM_RESTDefaultServer::containsHelpKeyWork(array_keys($_GET))) {
    OpenM_RESTDefaultServer::handle(array("OpenM_ID"));
} else if (isset($_GET[OpenM_ID::GetOpenID_API])) {
    OpenM_OpenID::getOpenID();
} else if (isset($_GET[OpenM_ID::URI_API])) {
    OpenM_OpenID::uriDisplay();
} else if (isset($_GET[OpenM_ID::LOGOUT_API])) {
    OpenM_OpenID::logout();
} else if (isset($_GET[OpenM_ID::LOGIN_API])) {
    Import::php("OpenM-ID.api.Impl.OpenM_IDReturnToController");
    $returnTo = new OpenM_IDReturnToController();
    OpenM_Header::redirect("user/?" . $returnTo->getReturnTo());
} else {
    OpenM_OpenID::handle();
}
?>