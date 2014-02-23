<?php

if (!Import::php("Smarty"))
    throw new ImportException("Smarty");

Import::php("OpenM-Services.gui.OpenM_ServiceView");
Import::php("util.OpenM_Log");
Import::php("OpenM-ID.api.Impl.OpenM_ID_ConnectedUserController");
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
 * @author Nicolas Rouzeaud & Gael SAUNIER
 */
class OpenM_IDView extends OpenM_ServiceView {

    const VERSION = "1.0 beta";
    const SPECIFIC_CONFIG_FILE_NAME = "OpenM_ID_Account.config.file.path";
    const HASH_SECRET = "OpenM_ID_Account.hash.secret";
    const HASH_ALGO = "OpenM_ID_Account.hash.algo";
    const DEFAULT_ACTIVATION = "OpenM_ID_Account.activation.default";
    const DEFAULT_ACTIVATION_OK = "true";

    private static $secret;
    private static $hashAlgo;
    private static $defaultAccountActivation = false;

    public function __construct() {
        parent::__construct();
        if (self::$secret !== null)
            return;
        $path = $this->properties->get(self::SPECIFIC_CONFIG_FILE_NAME);
        if ($path == null)
            throw new OpenM_ServiceViewException(self::SPECIFIC_CONFIG_FILE_NAME . " property is not defined in " . self::CONFIG_FILE_NAME);
        $p2 = Properties::fromFile(OpenM_SERVICE_CONFIG_DIRECTORY . "/" . $path);
        self::$secret = $p2->get(self::HASH_SECRET);
        if (self::$secret == null)
            throw new OpenM_ServiceViewException(self::HASH_SECRET . " property is not defined in $path");
        if ($p2->get(self::HASH_ALGO) == null)
            throw new OpenM_ServiceViewException(self::HASH_ALGO . " property is not defined in $path");
        self::$hashAlgo = $p2->get(self::HASH_ALGO);
        if (!OpenM_Crypto::isAlgoValid(self::$hashAlgo))
            throw new OpenM_ServiceViewException(self::HASH_ALGO . " property is not a valid crypto algo in $path");
        self::$defaultAccountActivation = ($p2->get(self::DEFAULT_ACTIVATION) == self::DEFAULT_ACTIVATION_OK);
    }

    public function _default() {
        $this->login();
    }

    const REMEMBER_ME_PARAMETER = "remember-me";
    const REMEMBER_ME_ON_PAREMETER_VALUE = "on";
    const MAIL_PARAMETER = "mail";
    const ERROR_PARAMETER = "error";
    const PASSWORD_PARAMETER = "password";
    const HEAD_PARAMETER = "head";

    public function login() {
        OpenM_Log::debug("API initialized", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("Check if a user is connected", __CLASS__, __METHOD__, __LINE__);
        $user = OpenM_ID_ConnectedUserController::get();
        if ($user != null)
            $this->connected($user);

        OpenM_Log::debug("No user connected", __CLASS__, __METHOD__, __LINE__);

        $post = HashtableString::from($_POST);
        $get = HashtableString::from($_GET);

        $rememberMe = ($post->get(self::REMEMBER_ME_PARAMETER) == self::REMEMBER_ME_ON_PAREMETER_VALUE);

        if ($post->containsKey(self::MAIL_PARAMETER)) {
            OpenM_Log::debug("A mail is given in POST", __CLASS__, __METHOD__, __LINE__);
            if (!OpenM_MailTool::isEMailValid($post->get(self::MAIL_PARAMETER))) {
                OpenM_Log::debug("Mail given is not a valid mail", __CLASS__, __METHOD__, __LINE__);
                $this->smarty->assign(self::ERROR_PARAMETER, array(
                    self::MAIL_PARAMETER => "mail not valid"
                ));
            } else {
                OpenM_Log::debug("Mail given is a valid mail (" . $_POST["mail"] . ")", __CLASS__, __METHOD__, __LINE__);
                if (!$post->containsKey(self::PASSWORD_PARAMETER) || $post->get(self::PASSWORD_PARAMETER) == "") {
                    OpenM_Log::debug("No password given", __CLASS__, __METHOD__, __LINE__);
                    $this->smarty->assign(self::ERROR_PARAMETER, array(
                        self::PASSWORD_PARAMETER => "password not valid"
                    ));
                } else {
                    OpenM_Log::debug("Password given not empty", __CLASS__, __METHOD__, __LINE__);
                    $userDao = new OpenM_UserDAO();
                    $user = $userDao->get($post->get(self::MAIL_PARAMETER));
                    if ($user == null) {
                        OpenM_Log::debug("User not found in DAO", __CLASS__, __METHOD__, __LINE__);
                        $this->smarty->assign(self::ERROR_PARAMETER, array(
                            self::HEAD_PARAMETER => "mail or password not valid"
                        ));
                    } else {
                        OpenM_Log::debug("User found in DAO", __CLASS__, __METHOD__, __LINE__);
                        $userPassword = self::getPassword($post->get(self::PASSWORD_PARAMETER));
                        if ($user->get(OpenM_UserDAO::USER_PASSWORD) != $userPassword) {
                            OpenM_Log::debug("Password not OK", __CLASS__, __METHOD__, __LINE__);
                            $this->smarty->assign(self::ERROR_PARAMETER, array(
                                self::HEAD_PARAMETER => "mail or password not valid"
                            ));
                        } else {
                            OpenM_Log::debug("Password OK", __CLASS__, __METHOD__, __LINE__);
                            if (!$user->get(OpenM_UserDAO::USER_IS_VALID)) {
                                OpenM_Log::debug("User not valid", __CLASS__, __METHOD__, __LINE__);
                                $this->smarty->assign(self::ERROR_PARAMETER, array(
                                    self::HEAD_PARAMETER => "user not validated",
                                ));
                            } else {
                                OpenM_Log::debug("User valid", __CLASS__, __METHOD__, __LINE__);
                                OpenM_ID_ConnectedUserController::set($user, $rememberMe);
                                self::connected($user);
                            }
                        }
                    }
                }
            }
        }
        $mail = $post->containsKey(self::MAIL_PARAMETER) ? $post->get(self::MAIL_PARAMETER) : ($get->containsKey(self::MAIL_PARAMETER) ? $get->get(self::MAIL_PARAMETER) : "");
        $this->smarty->assign("action", OpenM_URLViewController::from($this->getClass(), "login")->getURL());
        $this->smarty->assign("version", self::VERSION);
        $this->smarty->assign("mail", $mail);
        $this->smarty->assign("rememberMe", $rememberMe);
        $returnTo = new OpenM_ID_ReturnToController();
        $this->smarty->assign("return_to", $returnTo->getReturnTo());
        $this->setDirs();
        $this->addLinks();
        $this->smarty->assign("lang", OpenM_URLViewController::getLang());
        $this->smarty->display('login.tpl');
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
                            $this->_redirect("login");
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
            $this->smarty->assign("action", OpenM_URLViewController::from($this->getClass(), "create")->getURL());
            $this->smarty->assign("version", self::VERSION);
            $returnTo = new OpenM_ID_ReturnToController();
            $this->smarty->assign("return_to", $returnTo->getReturnTo());
            $this->setDirs();
            $this->addLinks();
            $this->smarty->assign("lang", OpenM_URLViewController::getLang());
            $this->smarty->display('create.tpl');
        }
    }

    private function connected(HashtableString $user) {
        if ($this->isReturnTo()) {
            OpenM_SessionController::remove(self::RETURN_TO_IN_SESSION);
            OpenM_Log::debug("return_to found and use for redirection", __CLASS__, __METHOD__, __LINE__);
            $returnTo = new OpenM_ID_ReturnToController();
            OpenM_Header::redirect($returnTo->getReturnTo());
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
        $this->smarty->assign("links", array(
            "login" => OpenM_URLViewController::from($this->getClass(), "login")->getURL(),
            "logout" => OpenM_URL::getDirURL() . "../" . "?" . OpenM_ID::LOGOUT_API,
            "create" => OpenM_URLViewController::from($this->getClass(), "create")->getURL()
        ));
    }

    private function setDirs() {
        $this->smarty->setTemplateDir(__DIR__ . '/tpl/');
        $this->smarty->setConfigDir(__DIR__ . '/config/');
        $this->smarty->setCompileDir($this->template_c);
        $this->smarty->setCacheDir($this->cache_dir);
        $this->smarty->assign(self::SMARTY_RESOURCES_DIR_VAR_NAME, $this->resources_dir);
    }

    private function getPassword($password) {
        return OpenM_Crypto::hash(self::$hashAlgo, OpenM_URL::encode(self::$secret . $password . self::$secret));
    }

    public function error404() {
        die("page not found");
    }

}

?>