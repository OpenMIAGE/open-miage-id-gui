<?php

if (!Import::php("Smarty"))
    throw new ImportException("Smarty");

Import::php("OpenM-Services.gui.OpenM_ServiceView");
Import::php("util.OpenM_Log");
Import::php("OpenM-ID.api.Impl.OpenM_ID_ConnectedUserController");

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
 * @author Nicolas Rouzeaud & Gael SAUNIER
 */
class OpenM_IDView extends OpenM_ServiceView {

    const VERSION = "1.0 beta";
    const SPECIFIC_CONFIG_FILE_NAME = "OpenM_ID_Account.config.file.path";
    const HASH_SECRET = "OpenM_ID_Account.hash.secret";
    const HASH_ALGO = "OpenM_ID_Account.hash.algo";
    const DEFAULT_ACTIVATION = "OpenM_ID_Account.activation.default";
    const DEFAULT_ACTIVATION_OK = "true";

    private $returnTo;
    private $smarty;
    private static $secret;
    private static $hashAlgo;
    private static $template_c;
    private static $resources_dir;
    private static $defaultAccountActivation = false;

    public function __construct() {
        parent::__construct();
        $this->smarty = new Smarty();
        self::init();
    }

    public function _default() {
        
    }

    private static function init() {
        if (self::$secret !== null)
            return;
        $p = Properties::fromFile(self::CONFIG_FILE_NAME);
        if ($p->get(self::LOG_MODE_PROPERTY) == self::LOG_MODE_ACTIVATED)
            OpenM_Log::init($p->get(self::LOG_PATH_PROPERTY), $p->get(self::LOG_LEVEL_PROPERTY), $p->get(self::LOG_FILE_NAME));
        $path = $p->get(self::SPECIFIC_CONFIG_FILE_NAME);
        if ($path == null)
            throw new OpenM_ServiceViewException(self::SPECIFIC_CONFIG_FILE_NAME . " property is not defined in " . self::CONFIG_FILE_NAME);
        $p2 = Properties::fromFile($path);
        self::$secret = $p2->get(self::HASH_SECRET);
        if (self::$secret == null)
            throw new OpenM_ServiceViewException(self::HASH_SECRET . " property is not defined in $path");
        if ($p2->get(self::HASH_ALGO) == null)
            throw new OpenM_ServiceViewException(self::HASH_ALGO . " property is not defined in $path");
        self::$hashAlgo = $p2->get(self::HASH_ALGO);
        if (!OpenM_Crypto::isAlgoValid(self::$hashAlgo))
            throw new OpenM_ServiceViewException(self::HASH_ALGO . " property is not a valid crypto algo in $path");
        self::$template_c = $p->get(self::SMARTY_TEMPLATE_C_DIR);
        if (self::$template_c == null)
            throw new OpenM_ServiceViewException(self::SMARTY_TEMPLATE_C_DIR . " not defined in " . self::CONFIG_FILE_NAME);
        self::$resources_dir = $p->get(self::RESOURCES_DIR);
        if (self::$resources_dir == null)
            throw new OpenM_ServiceViewException(self::RESOURCES_DIR . " not defined in " . self::CONFIG_FILE_NAME);
        self::$defaultAccountActivation = ($p2->get(self::DEFAULT_ACTIVATION) == self::DEFAULT_ACTIVATION_OK);
    }

    public function create() {
        OpenM_Log::debug("API initialized", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("check if a user is connected", __CLASS__, __METHOD__, __LINE__);
        $user = OpenM_ID_ConnectedUserController::get();
        if ($user != null) {
            OpenM_Log::debug("a user is connected (" . $user->get(OpenM_UserDAO::USER_ID) . ")", __CLASS__, __METHOD__, __LINE__);
            $this->connected($user);
        } else {
            OpenM_Log::debug("no user connected", __CLASS__, __METHOD__, __LINE__);
            if (isset($_POST["mail"])) {
                OpenM_Log::debug("A mail is given", __CLASS__, __METHOD__, __LINE__);
                if (!OpenM_MailTool::isEMailValid($_POST["mail"])) {
                    OpenM_Log::debug("Given mail is not valid", __CLASS__, __METHOD__, __LINE__);
                    $this->smarty->assign("error", array(
                        "mail" => "Mail not valid"
                    ));
                } else {
                    OpenM_Log::debug("Given mail is valid", __CLASS__, __METHOD__, __LINE__);
                    $userDao = new OpenM_UserDAO();
                    $user = $userDao->get($_POST["mail"]);
                    if ($user == null) {
                        OpenM_Log::debug("No user already registered with same mail", __CLASS__, __METHOD__, __LINE__);
                        $userId = OpenM_Crypto::hash(self::$hashAlgo, OpenM_URL::encode(self::$secret . $_POST["mail"] . microtime(true) . self::$secret));
                        OpenM_Log::debug("User id generated ($userId)", __CLASS__, __METHOD__, __LINE__);
                        $password = $_POST["password"];
                        if ($password == "") {
                            OpenM_Log::debug("Password not defined", __CLASS__, __METHOD__, __LINE__);
                            $this->smarty->assign("error", array(
                                "password" => "Password not defined"
                            ));
                        } else if ($password == $_POST["password2"]) {
                            $userPassword = $this->getPassword($password);
                            $userMail = $_POST["mail"];
                            $userDao->create($userId, $userMail, $userPassword, self::$defaultAccountActivation);
                            OpenM_Log::debug("User created", __CLASS__, __METHOD__, __LINE__);
                            OpenM_Log::debug("redirect to login page", __CLASS__, __METHOD__, __LINE__);
                            $this->redirectToLogin();
                        } else {
                            OpenM_Log::debug("Password2 not same as Password", __CLASS__, __METHOD__, __LINE__);
                            $this->smarty->assign("error", array(
                                "password2" => "Not the same password"
                            ));
                        }
                    } else {
                        OpenM_Log::debug("Mail already used by a registered user", __CLASS__, __METHOD__, __LINE__);
                        $this->smarty->assign("error", array(
                            "mail" => "Mail already used"
                        ));
                    }
                }
            }
            OpenM_Log::debug("Display create account page", __CLASS__, __METHOD__, __LINE__);
            $this->smarty->assign("mail", $_POST["mail"]);
            $this->smarty->assign("action", OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::CREATE_API);
            $this->smarty->assign("version", self::VERSION);
            $this->smarty->assign("return_to", $this->getReturnTo());
            $this->setDirs();
            $this->addLinks();
            $this->smarty->assign("lang", OpenM_URLViewController::getLang());
            $this->smarty->display('create.tpl');
        }
    }

    private function redirectToLogin() {
        OpenM_Header::redirect(OpenM_URLViewController::from(OpenM_URLViewController::viewFromClass($this->getClass()), "login")->getURL());
    }

    public function logout() {
        OpenM_ID_ConnectedUserController::remove();
        if ($this->isReturnTo())
            $this->returnTo();
        else {
            $this->redirectToLogin();
        }
    }

    private function connected(HashtableString $user) {
        if ($this->isReturnTo()) {
            OpenM_SessionController::remove(self::RETURN_TO_IN_SESSION);
            OpenM_Log::debug("return_to found and use for redirection", __CLASS__, __METHOD__, __LINE__);
            OpenM_Header::redirect($this->getReturnTo());
        } else {
            OpenM_Log::debug("display connected page", __CLASS__, __METHOD__, __LINE__);
            $this->smarty->assign("version", self::VERSION);
            $this->smarty->assign("mail", $user->get(OpenM_UserDAO::USER_MAIL));
            $this->smarty->assign("logout", OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGOUT_API);
            $this->setDirs();
            $this->addLinks();
            $this->smarty->display('connected.tpl');
        }
    }

    private function returnTo() {
        if (!isset($_GET[OpenM_ID::NO_REDIRECT_TO_LOGIN_PARAMETER]))
            OpenM_Header::redirect(OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGIN_API . (($this->isReturnTo()) ? "&return_to=" . $this->getReturnTo() : ""));
        else if ($this->isReturnTo())
            OpenM_Header::redirect($this->getReturnTo());
        else {
            OpenM_Log::warning("returnTo called without return_to parameter");
            $this->errorDisplay("internal error occur");
        }
        die();
    }

    private function getReturnTo() {
        if ($this->returnTo !== null)
            return $this->returnTo;

        $method = $_SERVER["REQUEST_METHOD"];
        switch ($method) {
            case "GET":
                $returnTo = $_GET["return_to"];
                break;
            case "POST":
                $returnTo = $_POST["return_to"];
                break;

            default:
                $returnTo = null;
                break;
        }

        if ($returnTo == null)
            $returnTo = OpenM_SessionController::get(self::RETURN_TO_IN_SESSION);

        OpenM_SessionController::set(self::RETURN_TO_IN_SESSION, $returnTo);
        $this->returnTo = OpenM_URL::decode($returnTo);

        return $this->returnTo;
    }

    private function isReturnTo() {
        return $this->getReturnTo() != null;
    }

    public function errorDisplay($message = null) {
        $this->smarty->assign("version", self::VERSION);
        $this->smarty->assign("message", $message);
        $this->setDirs();
        $this->addLinks();
        try {
            $this->smarty->display('error.tpl');
        } catch (Exception $e) {
            OpenM_Log::error($e->getTraceAsString());
            die("internal error occurs... try again later");
        }
        die();
    }

    private function addLinks() {
        $api = OpenM_URL::getDirURL();
        $this->smarty->assign("links", array(
            "login" => $api . "?" . OpenM_ID::LOGIN_API,
            "logout" => $api . "?" . OpenM_ID::LOGOUT_API,
            "create" => $api . "?" . OpenM_ID::CREATE_API
        ));
    }

    private function setDirs() {
        $this->smarty->setTemplateDir(__DIR__ . '/tpl/');
        $this->smarty->setConfigDir(__DIR__ . '/config/');
        $this->smarty->setCompileDir(self::$template_c);
        $this->smarty->assign(self::SMARTY_RESOURCES_DIR_VAR_NAME, self::$resources_dir);
    }

    private function getPassword($password) {
        return OpenM_Crypto::hash(self::$hashAlgo, OpenM_URL::encode(self::$secret . $password . self::$secret));
    }

}

?>