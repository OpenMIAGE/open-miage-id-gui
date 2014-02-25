<?php

Import::php("OpenM-Controller.api.OpenM_RESTDefaultServer");
Import::php("OpenM-ID.api.OpenM_ID");
Import::php("OpenM-ID.api.Impl.OpenM_ID_OpenID");
Import::php("util.Properties");
Import::php("OpenM-Services.gui.OpenM_ServiceView");
Import::php("OpenM-ID.api.Impl.OpenM_ID_ReturnToController");

/**
 * 
 * @package OpenM  
 * @subpackage OpenM\OpenM-ID\gui
 * @license http://www.apache.org/licenses/LICENSE-2.0 Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @link http://www.open-miage.org
 * @author Gael SAUNIER
 */
class OpenM_ID_APIServer {

    public function handle() {
        $property = Properties::fromFile(OpenM_SERVICE_CONFIG_FILE_NAME);
        OpenM_Log::init(OpenM_SERVICE_CONFIG_DIRECTORY
                . "/" . $property->get(OpenM_ServiceView::LOG_PATH_PROPERTY), $property->get(OpenM_ServiceView::LOG_LEVEL_PROPERTY), $property->get(OpenM_ServiceView::LOG_FILE_NAME), $property->get(OpenM_ServiceView::LOG_LINE_MAX_SIZE
        ));
        $returnTo = new OpenM_ID_ReturnToController();
        $openM_ID_Server = new OpenM_ID_OpenID();
        if (isset($_GET["api"]) || OpenM_RESTDefaultServer::containsHelpKeyWork(array_keys($_GET))) {
            OpenM_RESTDefaultServer::handle(array("OpenM_ID"));
        } else if (isset($_GET[OpenM_ID::GetOpenID_API])) {
            $openM_ID_Server->getOpenID();
        } else if (isset($_GET[OpenM_ID::URI_API])) {
            $openM_ID_Server->uriDisplay();
        } else if (isset($_GET[OpenM_ID::LOGOUT_API])) {
            $openM_ID_Server->logout(false);
        } else if (isset($_GET[OpenM_ID::LOGIN_API])) {
            // do nothing
        } else {
            $openM_ID_Server->handle();
            die(0);
        }
        $returnTo->save();
        OpenM_Header::redirect("user");
    }

}

?>