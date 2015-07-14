<?php

/**
* Simple ajaxy chat class
*/
class SimpleAjaxyChat {

    /**
    * constructor
    */
    function SimpleAjaxyChat() {}

    /**
    * Adding to DB table posted message
    */
 function acceptMessages() {
        $sUsername = $GLOBALS['aLMemInfo']['name'];
        $iUserID = (int)$GLOBALS['aLMemInfo']['id'];
	$roomme = $GLOBALS['myroom'];
        if($sUsername && isset($_POST['s_message']) && $_POST['s_message'] != '') {
            $sMessage = $GLOBALS['MySQL']->process_db_input($_POST['s_message'], A_TAGS_STRIP);
            if ($sMessage != '') {
                $GLOBALS['MySQL']->res("INSERT INTO `s_ajax_chat_messages` SET `member_id`='{$iUserID}', `member_name`='{$sUsername}', `message`='{$sMessage}' , `room`='{$roomme}', `when`=UNIX_TIMESTAMP()");
            }
        }
    }


    /**
    * Return input text form
    */
    function getInputForm() {
        ob_start();
        require_once('templates/chat_input.html');
        return ob_get_clean();
    }

    /**
    * Return last 15 messages
    */
    function getMessages($bOnlyMessages = false) {
	 $roomme = $GLOBALS['myroom'];
        $aMessages = $GLOBALS['MySQL']->getAll("SELECT `s_ajax_chat_messages`.*, `s_members`.`name`, UNIX_TIMESTAMP()-`s_ajax_chat_messages`.`when` AS 'diff' FROM `s_ajax_chat_messages` INNER JOIN `s_members` ON `s_members`.`id` = `s_ajax_chat_messages`.`member_id` WHERE `s_ajax_chat_messages`.`room` = '".$roomme."'   ORDER BY `id` DESC LIMIT 15");

print "<span style='color: #666; font-size: 12px; font-weight: normal; font-family: \'Open Sans\', sans-serif;'>Chat Room:".$roomme."</span><br>";

#print "SELECT `s_ajax_chat_messages`.*, `s_members`.`name`, UNIX_TIMESTAMP()-`s_ajax_chat_messages`.`when` AS 'diff' FROM `s_ajax_chat_messages` INNER JOIN `s_members` ON `s_members`.`id` = `s_ajax_chat_messages`.`member_id` WHERE `s_ajax_chat_messages`.`room` = '".$roomme."'   ORDER BY `id` DESC LIMIT 15";
        $sMessages = '';
        // collecting list of messages
        foreach ($aMessages as $iID => $aMessage) {
            $sExStyles = $sExJS = '';
            $iDiff = (int)$aMessage['diff'];
            if ($iDiff < 7) {
                $sExStyles = 'style="display:none;"';
                $sExJS = "<script> $('#message_{$aMessage['id']}').slideToggle('slow'); </script>";
            }

            $sWhen = date("H:i:s", $aMessage['when']);
            $sMessages .= '<div class="message" id="message_'.$aMessage['id'].'" '.$sExStyles.'><span class="dateme">[' . $sWhen . ']</span><span class="nameme">' . $aMessage['name'] . '</span>:<span class="mesame"> ' . $aMessage['message'] . '</span></div>' . $sExJS;
        }

        if ($bOnlyMessages) return $sMessages;
        return '<div class="chat_main">' . $sMessages . '</div>';
    }
}

$GLOBALS['AjaxChat'] = new SimpleAjaxyChat();

?>
