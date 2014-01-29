<?php

Import::php("OpenM-SSO.api.OpenM_SSO");
Import::php("util.http.OpenM_URL");
Import::php("util.http.OpenM_Header");
Import::php("OpenM-Services.api.Impl.OpenM_ServiceImpl");
Import::php("OpenM-Mail.api.OpenM_MailTool");
Import::php("OpenM-ID.api.Impl.DAO.OpenM_UserDAO");
Import::php("util.crypto.OpenM_Crypto");
Import::php("OpenM-ID.api.Impl.OpenM_ID_ConnectedUserController");
if (!Import::php("Smarty"))
    throw new ImportException("Smarty");

/**
 * Description of OpenM_ID_Account
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-ID\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_ID_Account extends OpenM_ServiceImpl {

    const VERSION = "1.0 beta";
    const SPECIFIC_CONFIG_FILE_NAME = "OpenM_ID_Account.config.file.path";
    const HASH_SECRET = "OpenM_ID_Account.hash.secret";
    const HASH_ALGO = "OpenM_ID_Account.hash.algo";
    const DEFAULT_ACTIVATION = "OpenM_ID_Account.activation.default";
    const DEFAULT_ACTIVATION_OK = "true";

    private static $returnTo;
    private static $secret;
    private static $hashAlgo;
    private static $template_c;
    private static $resources_dir;
    private static $defaultAccountActivation = false;

    private static function init() {
        $p = Properties::fromFile(self::CONFIG_FILE_NAME);
        if ($p->get(self::LOG_MODE_PROPERTY) == self::LOG_MODE_ACTIVATED)
            OpenM_Log::init($p->get(self::LOG_PATH_PROPERTY), $p->get(self::LOG_LEVEL_PROPERTY), $p->get(self::LOG_FILE_NAME));
        $path = $p->get(self::SPECIFIC_CONFIG_FILE_NAME);
        if ($path == null)
            throw new OpenM_ServiceImplException(self::SPECIFIC_CONFIG_FILE_NAME . " property is not defined in " . self::CONFIG_FILE_NAME);
        $p2 = Properties::fromFile($path);
        self::$secret = $p2->get(self::HASH_SECRET);
        if (self::$secret == null)
            throw new OpenM_ServiceImplException(self::HASH_SECRET . " property is not defined in $path");
        if ($p2->get(self::HASH_ALGO) == null)
            throw new OpenM_ServiceImplException(self::HASH_ALGO . " property is not defined in $path");
        self::$hashAlgo = $p2->get(self::HASH_ALGO);
        if (!OpenM_Crypto::isAlgoValid(self::$hashAlgo))
            throw new OpenM_ServiceImplException(self::HASH_ALGO . " property is not a valid crypto algo in $path");
        self::$template_c = $p->get(self::SMARTY_TEMPLATE_C_DIR);
        if (self::$template_c == null)
            throw new OpenM_ServiceImplException(self::SMARTY_TEMPLATE_C_DIR . " not defined in " . self::CONFIG_FILE_NAME);
        self::$resources_dir = $p->get(self::RESOURCES_DIR);
        if (self::$resources_dir == null)
            throw new OpenM_ServiceImplException(self::RESOURCES_DIR . " not defined in " . self::CONFIG_FILE_NAME);
        self::$defaultAccountActivation = ($p2->get(self::DEFAULT_ACTIVATION) == self::DEFAULT_ACTIVATION_OK);
    }

    public static function create() {
        self::init();
        OpenM_Log::debug("API initialized", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("check if a user is connected", __CLASS__, __METHOD__, __LINE__);
        $user = OpenM_ID_ConnectedUserController::get();
        if ($user != null) {
            OpenM_Log::debug("a user is connected (" . $user->get(OpenM_UserDAO::USER_ID) . ")", __CLASS__, __METHOD__, __LINE__);
            self::connected($user);
        } else {
            OpenM_Log::debug("no user connected", __CLASS__, __METHOD__, __LINE__);
            $smarty = new Smarty();
            if (isset($_POST["mail"])) {
                OpenM_Log::debug("A mail is given", __CLASS__, __METHOD__, __LINE__);
                if (!OpenM_MailTool::isEMailValid($_POST["mail"])) {
                    OpenM_Log::debug("Given mail is not valid", __CLASS__, __METHOD__, __LINE__);
                    $smarty->assign("error", array(
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
                            $smarty->assign("error", array(
                                "password" => "Password not defined"
                            ));
                        } else if ($password == $_POST["password2"]) {
                            $userPassword = self::getPassword($password);
                            $userMail = $_POST["mail"];
                            $userDao->create($userId, $userMail, $userPassword, self::$defaultAccountActivation);
                            OpenM_Log::debug("User created", __CLASS__, __METHOD__, __LINE__);
                            OpenM_Log::debug("redirect to login page", __CLASS__, __METHOD__, __LINE__);
                            OpenM_Header::redirect(OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGIN_API);
                        } else {
                            OpenM_Log::debug("Password2 not same as Password", __CLASS__, __METHOD__, __LINE__);
                            $smarty->assign("error", array(
                                "password2" => "Not the same password"
                            ));
                        }
                    } else {
                        OpenM_Log::debug("Mail already used by a registered user", __CLASS__, __METHOD__, __LINE__);
                        $smarty->assign("error", array(
                            "mail" => "Mail already used"
                        ));
                    }
                }
            }
            OpenM_Log::debug("Display create account page", __CLASS__, __METHOD__, __LINE__);
            $smarty->assign("mail", $_POST["mail"]);
            $smarty->assign("action", OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::CREATE_API);
            $smarty->assign("version", self::VERSION);
            $smarty->assign("return_to", self::getReturnTo());
            self::setDirs($smarty);
            self::addLinks($smarty);
            $smarty->assign("lang", "fr");
            $smarty->display('create.tpl');
        }
        die();
    }

    const REMEMBER_ME_PARAMETER = "remember-me";
    const REMEMBER_ME_ON_PAREMETER_VALUE = "on";
    const MAIL_PARAMETER = "mail";
    const ERROR_PARAMETER = "error";
    const PASSWORD_PARAMETER = "password";
    const HEAD_PARAMETER = "head";
    const EMBEDED_PARAMETER = "embeded";
    const RETURN_TO_IN_SESSION = "OpenM_ID.return_to";

    public static function login() {
        self::init();
        OpenM_Log::debug("API initialized", __CLASS__, __METHOD__, __LINE__);
        OpenM_Log::debug("Check if a user is connected", __CLASS__, __METHOD__, __LINE__);
        $user = OpenM_ID_ConnectedUserController::get();
        if ($user != null)
            self::connected($user);

        OpenM_Log::debug("No user connected", __CLASS__, __METHOD__, __LINE__);

        $smarty = new Smarty();

        $post = HashtableString::from($_POST);
        $get = HashtableString::from($_GET);

        $rememberMe = ($post->get(self::REMEMBER_ME_PARAMETER) == self::REMEMBER_ME_ON_PAREMETER_VALUE);

        if ($post->containsKey(self::MAIL_PARAMETER)) {
            OpenM_Log::debug("A mail is given in POST", __CLASS__, __METHOD__, __LINE__);
            if (!OpenM_MailTool::isEMailValid($post->get(self::MAIL_PARAMETER))) {
                OpenM_Log::debug("Mail given is not a valid mail", __CLASS__, __METHOD__, __LINE__);
                $smarty->assign(self::ERROR_PARAMETER, array(
                    self::MAIL_PARAMETER => "mail not valid"
                ));
            } else {
                OpenM_Log::debug("Mail given is a valid mail (" . $_POST["mail"] . ")", __CLASS__, __METHOD__, __LINE__);
                if (!$post->containsKey(self::PASSWORD_PARAMETER) || $post->get(self::PASSWORD_PARAMETER) == "") {
                    OpenM_Log::debug("No password given", __CLASS__, __METHOD__, __LINE__);
                    $smarty->assign(self::ERROR_PARAMETER, array(
                        self::PASSWORD_PARAMETER => "password not valid"
                    ));
                } else {
                    OpenM_Log::debug("Password given not empty", __CLASS__, __METHOD__, __LINE__);
                    $userDao = new OpenM_UserDAO();
                    $user = $userDao->get($post->get(self::MAIL_PARAMETER));
                    if ($user == null) {
                        OpenM_Log::debug("User not found in DAO", __CLASS__, __METHOD__, __LINE__);
                        $smarty->assign(self::ERROR_PARAMETER, array(
                            self::HEAD_PARAMETER => "mail or password not valid"
                        ));
                    } else {
                        OpenM_Log::debug("User found in DAO", __CLASS__, __METHOD__, __LINE__);
                        $userPassword = self::getPassword($post->get(self::PASSWORD_PARAMETER));
                        if ($user->get(OpenM_UserDAO::USER_PASSWORD) != $userPassword) {
                            OpenM_Log::debug("Password not OK", __CLASS__, __METHOD__, __LINE__);
                            $smarty->assign(self::ERROR_PARAMETER, array(
                                self::HEAD_PARAMETER => "mail or password not valid"
                            ));
                        } else {
                            OpenM_Log::debug("Password OK", __CLASS__, __METHOD__, __LINE__);
                            if (!$user->get(OpenM_UserDAO::USER_IS_VALID)) {
                                OpenM_Log::debug("User not valid", __CLASS__, __METHOD__, __LINE__);
                                $smarty->assign(self::ERROR_PARAMETER, array(
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
        $smarty->assign("action", OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGIN_API);
        $smarty->assign("version", self::VERSION);
        $smarty->assign("mail", $mail);
        $smarty->assign("rememberMe", $rememberMe);
        $smarty->assign("return_to", self::getReturnTo());
        self::setDirs($smarty);
        self::addLinks($smarty);
        $smarty->assign("lang", "fr");
        if ($post->containsKey(self::EMBEDED_PARAMETER) || $get->containsKey(self::EMBEDED_PARAMETER))
            $smarty->display('login_embeded.tpl');
        else
            $smarty->display('login.tpl');
    }

    public static function logout() {
        self::init();
        OpenM_ID_ConnectedUserController::remove();
        if (self::isReturnTo())
            self::returnTo();
        else {
            OpenM_Header::redirect(OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGIN_API);
        }
        die();
    }

    public static function uriDisplay() {
        self::init();
        $smarty = new Smarty();
        $smarty->assign("api", OpenM_URL::getURLwithoutParameters());
        $smarty->assign("version", self::VERSION);
        $id = $_GET[OpenM_ID::URI_API];
        $smarty->assign("ID", $id);
        $smarty->assign("URI", OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::URI_API . "=" . $id);
        self::setDirs($smarty);
        self::addLinks($smarty);
        $smarty->display('uri.tpl');
        die();
    }

    public static function getOpenID() {
        self::init();
        if (!self::isReturnTo())
            self::errorDisplay("No return_to parameter found");

        $return_to = self::getReturnTo();

        if (RegExp::ereg(OpenM_ID::OID_PARAMETER . "=", $return_to)) {
            exit(0);
        }

        if (RegExp::ereg(str_replace("www.", "", OpenM_ID::OID_PARAMETER . "="), $return_to)) {
            self::errorDisplay("");
        }

        $user = OpenM_ID_ConnectedUserController::get();

        if (!isset($_GET[OpenM_ID::NO_REDIRECT_TO_LOGIN_PARAMETER]) && $user == null)
            OpenM_Header::redirect(OpenM_URL::getDirURL() . "?" . (isset($_GET[self::EMBEDED_PARAMETER]) ? self::EMBEDED_PARAMETER . "&" : "") . OpenM_ID::LOGIN_API . "&return_to=" . OpenM_URL::encode());

        if (RegExp::ereg("\?", $return_to))
            $return_to .= "&" . OpenM_ID::OID_PARAMETER . "=";
        else
            $return_to .= "?" . OpenM_ID::OID_PARAMETER . "=";

        $return_to .= (isset($_GET[OpenM_ID::NO_REDIRECT_TO_LOGIN_PARAMETER]) && $user == null) ? OpenM_SSO::RETURN_ERROR_MESSAGE_NOT_CONNECTED_VALUE : OpenM_URL::encode(OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::URI_API . "=" . $user->get(OpenM_UserDAO::USER_ID));

        OpenM_Header::redirect($return_to);
        die();
    }

    private static function connected(HashtableString $user) {
        if (self::isReturnTo()) {
            OpenM_SessionController::remove(self::RETURN_TO_IN_SESSION);
            OpenM_Log::debug("return_to found and use for redirection", __CLASS__, __METHOD__, __LINE__);
            OpenM_Header::redirect(self::getReturnTo());
        } else {
            OpenM_Log::debug("display connected page", __CLASS__, __METHOD__, __LINE__);
            $smarty = new Smarty();
            $smarty->assign("version", self::VERSION);
            $smarty->assign("mail", $user->get(OpenM_UserDAO::USER_MAIL));
            $smarty->assign("logout", OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGOUT_API);
            self::setDirs($smarty);
            self::addLinks($smarty);
            $smarty->display('connected.tpl');
        }
        die();
    }

    private static function returnTo() {
        if (!isset($_GET[OpenM_ID::NO_REDIRECT_TO_LOGIN_PARAMETER]))
            OpenM_Header::redirect(OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::LOGIN_API . ((self::isReturnTo()) ? "&return_to=" . self::getReturnTo() : ""));
        else if (self::isReturnTo())
            OpenM_Header::redirect(self::getReturnTo());
        else {
            OpenM_Log::warning("returnTo called without return_to parameter");
            self::errorDisplay("internal error occur");
        }
        die();
    }

    private static function getReturnTo() {
        if (self::$returnTo !== null)
            return self::$returnTo;

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
        self::$returnTo = OpenM_URL::decode($returnTo);

        return self::$returnTo;
    }

    private static function isReturnTo() {
        return self::getReturnTo() != null;
    }

    public static function errorDisplay($message = null) {
        $smarty = new Smarty();
        $smarty->assign("version", self::VERSION);
        $smarty->assign("message", $message);
        self::setDirs($smarty);
        self::addLinks($smarty);
        try {
            $smarty->display('error.tpl');
        } catch (Exception $e) {
            OpenM_Log::error($e->getTraceAsString());
            die("internal error occurs... try again later");
        }
        die();
    }

    private static function getPassword($password) {
        return OpenM_Crypto::hash(self::$hashAlgo, OpenM_URL::encode(self::$secret . $password . self::$secret));
    }

    private static function addLinks(Smarty $smarty) {
        $api = OpenM_URL::getDirURL();
        $smarty->assign("links", array(
            "login" => $api . "?" . OpenM_ID::LOGIN_API,
            "logout" => $api . "?" . OpenM_ID::LOGOUT_API,
            "create" => $api . "?" . OpenM_ID::CREATE_API
        ));
    }

    private static function setDirs(Smarty $smarty) {
        $smarty->setTemplateDir(dirname(dirname(__DIR__)) . '/gui/tpl/');
        $smarty->setConfigDir(dirname(dirname(__DIR__)) . '/gui/config/');
        $smarty->setCompileDir(self::$template_c);
        $smarty->assign(self::SMARTY_RESOURCES_DIR_VAR_NAME, self::$resources_dir);
    }

    private static function display(Smarty $s, $tpl) {
        try {
            $s->display($tpl);
        } catch (Exception $e) {
            die("internal error occurs... try again later");
        }
    }

}

?>