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
class OpenM_IDView extends OpenM_IDCommonsView {

    const SPECIFIC_CONFIG_FILE_NAME = "OpenM_ID_Account.config.file.path";
    const HASH_SECRET = "OpenM_ID_Account.hash.secret";
    const HASH_ALGO = "OpenM_ID_Account.hash.algo";
    const DEFAULT_ACTIVATION = "OpenM_ID_Account.activation.default";
    const DEFAULT_ACTIVATION_OK = "true";
    const DEFAULT_REMEMBER_ME = "OpenM_ID_Account.remember-me.default";
    const DEFAULT_REMEMBER_ME_OK = "true";

    private $secret;
    private $hashAlgo;
    private $defaultAccountActivation = false;
    private $defaultRememberMe = false;

    public function __construct() {
        parent::__construct();
        $path = $this->properties->get(self::SPECIFIC_CONFIG_FILE_NAME);
        if ($path == null)
            throw new OpenM_ServiceViewException(self::SPECIFIC_CONFIG_FILE_NAME . " property is not defined in " . self::CONFIG_FILE_NAME);
        $p2 = Properties::fromFile(dirname(self::CONFIG_FILE_NAME) . "/" . $path);
        $this->secret = $p2->get(self::HASH_SECRET);
        if ($this->secret == null)
            throw new OpenM_ServiceViewException(self::HASH_SECRET . " property is not defined in $path");
        if ($p2->get(self::HASH_ALGO) == null)
            throw new OpenM_ServiceViewException(self::HASH_ALGO . " property is not defined in $path");
        $this->hashAlgo = $p2->get(self::HASH_ALGO);
        if (!OpenM_Crypto::isAlgoValid($this->hashAlgo))
            throw new OpenM_ServiceViewException(self::HASH_ALGO . " property is not a valid crypto algo in $path");
        $this->defaultAccountActivation = ($p2->get(self::DEFAULT_ACTIVATION) == self::DEFAULT_ACTIVATION_OK);
        $this->defaultRememberMe = ($p2->get(self::DEFAULT_REMEMBER_ME) == self::DEFAULT_REMEMBER_ME_OK);
    }

    public function _default() {
        $this->login();
    }

    const REMEMBER_ME_PARAMETER = "remember_me";
    const SMARTY_REMEMBER_ME = self::REMEMBER_ME_PARAMETER;
    const REMEMBER_ME_ON_PAREMETER_VALUE = "on";
    const MAIL_PARAMETER = "mail";
    const SMARTY_MAIL = self::MAIL_PARAMETER;
    const SMARTY_ERROR = "error";
    const PASSWORD_PARAMETER = "password";
    const PASSWORD2_PARAMETER = "password2";
    const SMARTY_PASSWORD = self::PASSWORD_PARAMETER;
    const SMARTY_PASSWORD2 = self::PASSWORD2_PARAMETER;
    const SMARTY_HEAD = "head";
    const SMARTY_ACTION = "action";
    const SMARTY_LOGIN_NOT_VALID = "login_not_valid";
    const SMARTY_PASSWORD_NOT_VALID = "password_not_valid";
    const SMARTY_MAIL_OR_PASSWORD_NOT_VALID = "mail_or_password_not_valid";
    const SMARTY_USER_NOT_VALIDATED = "user_not_validated";
    const SMARTY_IS_RESPONSE = "isResponse";
    const SMARTY_VERSION = "version";
    const SMARTY_RETURN_TO = "return_to";

    public function login() {
        OpenM_Log::debug("API initialized", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("Check if a user is connected", __CLASS__, __METHOD__, __LINE__);
        $userConnectedController = new OpenM_ID_ConnectedUserController();
        $user = $userConnectedController->get();
        if ($user != null)
            $this->connected($user);

        OpenM_Log::debug("No user connected", __CLASS__, __METHOD__, __LINE__);

        $post = HashtableString::from($_POST);
        $get = HashtableString::from($_GET);

        $error = array();
        if ($post->containsKey(self::MAIL_PARAMETER)) {
            $this->smarty->assign(self::SMARTY_IS_RESPONSE, true);
            OpenM_Log::debug("A mail is given in POST", __CLASS__, __METHOD__, __LINE__);
            if (!OpenM_MailTool::isEMailValid($post->get(self::MAIL_PARAMETER))) {
                OpenM_Log::debug("Mail given is not a valid mail", __CLASS__, __METHOD__, __LINE__);
                $error = array(
                    self::MAIL_PARAMETER => self::SMARTY_LOGIN_NOT_VALID
                );
            } else {
                OpenM_Log::debug("Mail given is a valid mail (" . $_POST["mail"] . ")", __CLASS__, __METHOD__, __LINE__);
                if (!$post->containsKey(self::PASSWORD_PARAMETER) || $post->get(self::PASSWORD_PARAMETER) == "") {
                    OpenM_Log::debug("No password given", __CLASS__, __METHOD__, __LINE__);
                    $error = array(
                        self::SMARTY_PASSWORD => self::SMARTY_PASSWORD_NOT_VALID
                    );
                } else {
                    OpenM_Log::debug("Password given not empty", __CLASS__, __METHOD__, __LINE__);
                    $userDao = new OpenM_UserDAO();
                    $user = $userDao->get(strtolower($post->get(self::MAIL_PARAMETER)));
                    if ($user == null) {
                        OpenM_Log::debug("User not found in DAO", __CLASS__, __METHOD__, __LINE__);
                        $error = array(
                            self::SMARTY_HEAD => self::SMARTY_MAIL_OR_PASSWORD_NOT_VALID
                        );
                    } else {
                        OpenM_Log::debug("User found in DAO", __CLASS__, __METHOD__, __LINE__);
                        $userPassword = $this->getPassword($post->get(self::PASSWORD_PARAMETER));
                        if ($user->get(OpenM_UserDAO::USER_PASSWORD) != $userPassword) {
                            OpenM_Log::debug("Password not OK", __CLASS__, __METHOD__, __LINE__);
                            $error = array(
                                self::SMARTY_PASSWORD => self::SMARTY_PASSWORD_NOT_VALID
                            );
                        } else {
                            OpenM_Log::debug("Password OK", __CLASS__, __METHOD__, __LINE__);
                            if (!$user->get(OpenM_UserDAO::USER_IS_VALID)) {
                                OpenM_Log::debug("User not valid", __CLASS__, __METHOD__, __LINE__);
                                $error = array(
                                    self::SMARTY_HEAD => self::SMARTY_USER_NOT_VALIDATED
                                );
                            } else {
                                OpenM_Log::debug("User valid", __CLASS__, __METHOD__, __LINE__);
                                $userConnectedController->set($user, $this->defaultRememberMe);
                                $this->connected($user);
                            }
                        }
                    }
                }
            }
        }
        $mail = $post->containsKey(self::MAIL_PARAMETER) ? $post->get(self::MAIL_PARAMETER) : ($get->containsKey(self::MAIL_PARAMETER) ? $get->get(self::MAIL_PARAMETER) : "");
        $this->smarty->assign(self::SMARTY_ERROR, $error);
        $this->smarty->assign(self::SMARTY_ACTION, OpenM_URLViewController::from($this->getClass(), "login")->getURL());
        $this->smarty->assign(self::SMARTY_VERSION, self::VERSION);
        $this->smarty->assign(self::MAIL_PARAMETER, $mail);
        $this->smarty->assign(self::REMEMBER_ME_PARAMETER, $this->defaultRememberMe);
        $returnTo = new OpenM_ID_ReturnToController();
        $this->smarty->assign(self::SMARTY_RETURN_TO, $returnTo->getReturnTo());
        $this->setDirs();
        $this->addLinks();
        $this->setLang();
        $this->smarty->display('login.tpl');
    }

    const SMARTY_LOGIN_ALREADY_USED = "login_already_used";
    const SMARTY_PASSWORD_NOT_FOUND = "password_not_found";
    const SMARTY_PASSWORD2_NOT_FOUND = "password2_not_found";
    const SMARTY_NOT_THE_SAME_PASSWORD = "not_the_same_password";

    public function register() {
        OpenM_Log::debug("API initialized", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("check if a user is connected", __CLASS__, __METHOD__, __LINE__);
        $userConnectedController = new OpenM_ID_ConnectedUserController();
        $user = $userConnectedController->get();
        $error = array();
        if ($user != null) {
            OpenM_Log::debug("a user is connected (" . $user->get(OpenM_UserDAO::USER_ID) . ")", __CLASS__, __METHOD__, __LINE__);
            $this->connected($user);
        } else {
            OpenM_Log::debug("no user connected", __CLASS__, __METHOD__, __LINE__);
            $post = HashtableString::from($_POST);
            if ($post->containsKey(self::MAIL_PARAMETER)) {
                $this->smarty->assign(self::SMARTY_IS_RESPONSE, true);
                OpenM_Log::debug("A mail is given", __CLASS__, __METHOD__, __LINE__);
                if (!OpenM_MailTool::isEMailValid($post->get(self::MAIL_PARAMETER))) {
                    OpenM_Log::debug("Given mail is not valid", __CLASS__, __METHOD__, __LINE__);
                    $error = array(
                        self::SMARTY_MAIL => self::SMARTY_LOGIN_NOT_VALID
                    );
                } else {
                    OpenM_Log::debug("Given mail is valid", __CLASS__, __METHOD__, __LINE__);
                    $userDao = new OpenM_UserDAO();
                    $user = $userDao->get(strtolower($post->get(self::MAIL_PARAMETER)));
                    if ($user == null) {
                        OpenM_Log::debug("No user already registered with same mail", __CLASS__, __METHOD__, __LINE__);
                        $userId = OpenM_Crypto::hash($this->hashAlgo, OpenM_URL::encode($this->secret . $post->get(self::MAIL_PARAMETER) . microtime(true) . $this->secret));
                        OpenM_Log::debug("User id generated ($userId)", __CLASS__, __METHOD__, __LINE__);
                        if ($post->get(self::PASSWORD_PARAMETER) == "") {
                            OpenM_Log::debug("Password not defined", __CLASS__, __METHOD__, __LINE__);
                            $error = array(
                                self::SMARTY_PASSWORD => self::SMARTY_PASSWORD_NOT_FOUND
                            );
                        } else if ($post->get(self::PASSWORD_PARAMETER) == $post->get(self::PASSWORD2_PARAMETER)) {
                            $userPassword = $this->getPassword($post->get(self::PASSWORD_PARAMETER));
                            $userMail = strtolower($post->get(self::MAIL_PARAMETER));
                            $userDao->create($userId, $userMail, $userPassword, $this->defaultAccountActivation);
                            OpenM_Log::debug("User created", __CLASS__, __METHOD__, __LINE__);
                            OpenM_Log::debug("redirect to login page", __CLASS__, __METHOD__, __LINE__);
                            $this->login();
                        } else {
                            if ($post->get(self::PASSWORD2_PARAMETER) == "")
                                $error = array(
                                    self::SMARTY_PASSWORD2 => self::SMARTY_PASSWORD2_NOT_FOUND
                                );
                            else {
                                OpenM_Log::debug("Password2 not same as Password", __CLASS__, __METHOD__, __LINE__);
                                $error = array(
                                    self::SMARTY_HEAD => self::SMARTY_NOT_THE_SAME_PASSWORD
                                );
                            }
                        }
                    } else {
                        OpenM_Log::debug("Mail already used by a registered user", __CLASS__, __METHOD__, __LINE__);
                        $error = array(
                            self::SMARTY_MAIL => self::SMARTY_LOGIN_ALREADY_USED
                        );
                    }
                }
            }
            OpenM_Log::debug("Display create account page", __CLASS__, __METHOD__, __LINE__);
            $this->smarty->assign(self::SMARTY_ERROR, $error);
            $this->smarty->assign(self::MAIL_PARAMETER, $post->get(self::MAIL_PARAMETER));
            $this->smarty->assign(self::SMARTY_ACTION, OpenM_URLViewController::from($this->getClass(), "register")->getURL());
            $this->smarty->assign(self::SMARTY_VERSION, self::VERSION);
            $returnTo = new OpenM_ID_ReturnToController();
            $this->smarty->assign(self::SMARTY_RETURN_TO, $returnTo->getReturnTo());
            $this->setDirs();
            $this->addLinks();
            $this->setLang();
            $this->smarty->display('create.tpl');
        }
    }

    private function connected(HashtableString $user) {
        $returnTo = new OpenM_ID_ReturnToController();
        if ($returnTo->isReturnTo()) {
            $returnTo->removeFromSession();
            OpenM_Log::debug("return_to found and use for redirection", __CLASS__, __METHOD__, __LINE__);
            OpenM_Header::redirect($returnTo->getReturnTo());
        } else {
            OpenM_Log::debug("display connected page", __CLASS__, __METHOD__, __LINE__);
            $this->setLang();
            $this->smarty->assign(self::SMARTY_MAIL, $user->get(OpenM_UserDAO::USER_MAIL));
            $this->setDirs();
            $this->addLinks();
            $this->smarty->display('connected.tpl');
            die();
        }
    }

    private function getPassword($password) {
        return OpenM_Crypto::hash($this->hashAlgo, OpenM_URL::encode($this->secret . $password . $this->secret));
    }

}

?>