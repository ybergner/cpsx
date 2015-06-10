<?php

/**
* Simple Login System class
*/
class SimpleLoginSystem {

    // variables
    var $bLoggedIn;

    /**
    * constructor
    */
    function SimpleLoginSystem() {
        $this->bLoggedIn = false;
        if ($_COOKIE['member_name'] && $_COOKIE['member_pass']) {
            if ($this->check_login($_COOKIE['member_name'], $_COOKIE['member_pass'], true)) {
                $this->bLoggedIn = true;
            }
        }
        $GLOBALS['bLoggedIn'] = $this->bLoggedIn;
    }

    function getLoginBox() {
        ob_start();
        require_once('templates/login_form.html');
        $sLoginForm = ob_get_clean();

        $sLogoutForm = '<a href="'.$_SERVER['PHP_SELF'].'?logout=1">Logout</a><hr />';

        if ((int)$_REQUEST['logout'] == 1) {
            $this->simple_logout();
            header("Location: index.php"); exit;
        }

        if ($_REQUEST['username'] && $_REQUEST['password']) {
            if ($this->check_login($_REQUEST['username'], MD5($_REQUEST['password']))) {
                $this->simple_login($_REQUEST['username'], $_REQUEST['password']);

                header("Location: index.php"); exit;
            } else {
                return 'Username or Password is incorrect' . $sLoginForm;
            }
        } else {
            if ($_COOKIE['member_name'] && $_COOKIE['member_pass']) {
                if ($this->check_login($_COOKIE['member_name'], $_COOKIE['member_pass'])) {
                    return 'Hello ' . $_COOKIE['member_name'] . '! ' . $sLogoutForm;
                }
            }
            return $sLoginForm;
        }
    }

    function simple_login($sName, $sPass) {
        $this->simple_logout();

        $sMd5Password = MD5($sPass);

        $iCookieTime = time() + 24*60*60*30;
        setcookie("member_name", $sName, $iCookieTime, '/');
        $_COOKIE['member_name'] = $sName;
        setcookie("member_pass", $sMd5Password, $iCookieTime, '/');
        $_COOKIE['member_pass'] = $sMd5Password;
    }

    function simple_logout() { 
        setcookie('member_name', '', time() - 96 * 3600, '/');
        setcookie('member_pass', '', time() - 96 * 3600, '/');

        unset($_COOKIE['member_name']);
        unset($_COOKIE['member_pass']);
    }

    function check_login($sName, $sPass, $bSetGlobals = false) {
        $sNameSafe = $GLOBALS['MySQL']->process_db_input($sName, A_TAGS_STRIP);
        $sPassSafe = $GLOBALS['MySQL']->process_db_input($sPass, A_TAGS_STRIP);

        $sSQL = "SELECT `id` FROM `s_members` WHERE `name`='{$sNameSafe}' AND `pass`='{$sPassSafe}'";
        $iID = (int)$GLOBALS['MySQL']->getOne($sSQL);

        if ($bSetGlobals) {
            $this->setLoggedMemberInfo($iID);
        }

        return ($iID > 0);
    }

    function setLoggedMemberInfo($iMemberID) {
        $sSQL = "SELECT * FROM `s_members` WHERE `id`='{$iMemberID}'";
        $aMemberInfos = $GLOBALS['MySQL']->getAll($sSQL);
        $GLOBALS['aLMemInfo'] = $aMemberInfos[0];
    }
}

$GLOBALS['oSimpleLoginSystem'] = new SimpleLoginSystem();

?>
