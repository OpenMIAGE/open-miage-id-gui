<?php

require_once 'config.php';
require_once 'src.php';
require_once 'lib.php';

Import::php("util.Properties");
Import::php("OpenM-Services.gui.OpenM_ServiceView");
$property = Properties::fromFile(OpenM_SERVICE_CONFIG_FILE_NAME);
OpenM_Log::init(OpenM_SERVICE_CONFIG_DIRECTORY . "/" . $property->get(OpenM_ServiceView::LOG_PATH_PROPERTY), $property->get(OpenM_ServiceView::LOG_LEVEL_PROPERTY), $property->get(OpenM_ServiceView::LOG_FILE_NAME), $property->get(OpenM_ServiceView::LOG_LINE_MAX_SIZE));

Import::php("OpenM-Controller.api.OpenM_RESTDefaultServer");
Import::php("OpenM-ID.api.OpenM_ID");
Import::php("OpenM-ID.api.Impl.OpenM_ID_OpenID");

if (isset($_GET["api"]) || OpenM_RESTDefaultServer::containsHelpKeyWork(array_keys($_GET))) {
    OpenM_RESTDefaultServer::handle(array("OpenM_ID"));
} else if (isset($_GET[OpenM_ID::GetOpenID_API])) {
    OpenM_ID_OpenID::getOpenID();
} else if (isset($_GET[OpenM_ID::URI_API])) {
    OpenM_ID_OpenID::uriDisplay();
} else if (isset($_GET[OpenM_ID::LOGOUT_API])) {
    OpenM_ID_OpenID::logout();
} else if (isset($_GET[OpenM_ID::LOGIN_API])) {
    Import::php("OpenM-ID.api.Impl.OpenM_ID_ReturnToController");
    $returnTo = new OpenM_ID_ReturnToController();
    $returnTo->save();
    OpenM_Header::redirect("user");
} else {
    OpenM_ID_OpenID::handle();
}
?>