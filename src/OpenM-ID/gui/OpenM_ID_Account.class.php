<?php
Import::php("OpenM-SSO.api.OpenM_SSO");
Import::php("util.http.OpenM_URL");
Import::php("util.http.OpenM_Header");
Import::php("OpenM-Services.api.Impl.OpenM_ServiceImpl");
Import::php("OpenM-Mail.api.OpenM_MailTool");
Import::php("OpenM-ID.api.Impl.DAO.OpenM_UserDAO");
Import::php("util.crypto.OpenM_Crypto");
Import::php("OpenM-ID.api.Impl.OpenM_ID_ConnectedUserController");

/**
 * Description of OpenM_ID_Account
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-ID\api\Impl 
 * @author Gaël Saunier
 */
class OpenM_ID_Account extends OpenM_ServiceImpl {

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

    public static function uriDisplay() {
        $id = $_GET[OpenM_ID::URI_API];
        ?>
        <html>
            <head>    
                <link rel="openid.server openid2.provider" href="<?php echo OpenM_URL::getURLwithoutParameters(); ?>" />
                <link rel="openid.delegate openid2.local_id" href="<?php echo OpenM_URL::getURLwithoutParameters() . "?" . OpenM_ID::URI_API . "=" . $id; ?>" />
            </head>
            <body>
                <h1>OpenM-ID server v<?php echo self::VERSION; ?> : OpenID</h1>
                Hello <?php echo $id; ?>
                <br>
            </body>
        </html>
        <?php
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

    private static function display(Smarty $s, $tpl) {
        try {
            $s->display($tpl);
        } catch (Exception $e) {
            die("internal error occurs... try again later");
        }
    }

}
?>