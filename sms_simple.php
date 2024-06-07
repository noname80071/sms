<?php

$text = $_GET['text'];
$sim = $_GET['sim'];


function convertToUTF8($str) {
    $enc = mb_detect_encoding($str);

    if ($enc && $enc != 'UTF-8') {
        return iconv($enc, 'UTF-8', $str);
    } else {
        return iconv('ucs-2be', 'UTF-8', $str);
    }
}

$message = convertToUTF8($text);


function SendSIMPLEmsg($To, $From, $Body) {

    $oSocket = fsockopen(localhost, 5038, $errnum, $errdesc) or die("Connection to host failed");
    fputs($oSocket, "Action: login\r\n");
    fputs($oSocket, "Events: off\r\n");
    fputs($oSocket, "Username: suser\r\n");
    fputs($oSocket, "Secret: secret\r\n\r\n");

    fputs($oSocket, "Action: MessageSend\r\n");
    fputs($oSocket, "To: $To\r\n");
    fputs($oSocket, "From: $From\r\n");
    fputs($oSocket, "Body: $Body\r\n");
    fputs($oSocket, "Action: Logoff\r\n\r\n");
    Sleep(2);
    fclose($oSocket);

}


switch ($sim) {
    case goip3202:
   $extension1 = "pjsip:101";
   $extension2 = "pjsip:102";
   SendSIMPLEmsg($extension1, $sim, $message);
   SendSIMPLEmsg($extension2, $sim, $message);
        break;
    case goip3209:
   $extension = "pjsip:101";
   SendSIMPLEmsg($extension, $sim, $message);
        break;
    case goip1602:
   $extension = "pjsip:102";
   SendSIMPLEmsg($extension, $sim, $message);
        break;
}


?>
