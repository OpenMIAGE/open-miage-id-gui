<?php

Import::php("OpenM-Services.gui.OpenM_ServiceView");
Import::php("util.OpenM_Log");

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
abstract class OpenM_IDCommonsView extends OpenM_ServiceView {

    const VERSION = "1.0 beta";


    protected function addLinks() {
        $this->smarty->assign("links", array(
            "login" => OpenM_URLViewController::from($this->getClass(), "login")->getURL(),
            "logout" => OpenM_URL::getDirURL() . "../" . "?" . OpenM_ID::LOGOUT_API,
            "create" => OpenM_URLViewController::from($this->getClass(), "create")->getURL()
        ));
    }

    protected function setDirs() {
        $this->smarty->setTemplateDir(__DIR__ . '/tpl/');
        $this->smarty->setConfigDir(__DIR__ . '/config/');
        $this->smarty->setCompileDir($this->template_c);
        $this->smarty->setCacheDir($this->cache_dir);
        $this->smarty->assign(self::SMARTY_RESOURCES_DIR_VAR_NAME, $this->resources_dir);
    }

}

?>