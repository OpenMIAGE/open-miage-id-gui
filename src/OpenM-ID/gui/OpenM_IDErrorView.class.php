<?php

Import::php("OpenM-ID.api.Impl.OpenM_ID_ConnectedUserController");
Import::php("OpenM-ID.api.Impl.OpenM_ID_ReturnToController");
Import::php("OpenM-ID.gui.OpenM_IDCommonsView");

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
class OpenM_IDErrorView extends OpenM_IDCommonsView {

    public function _default() {
        $this->error404();
    }

    public function error404() {
        OpenM_Header::add("page not found",404);
        die("page not found");
    }
}

?>