<?php

$input = file_get_contents('php://input');
$data = json_decode($input);


// chat_id получателей
$settings = parse_ini_file("tg-settings.ini");

// Токен для бота
$token = "6090381746:AAHti0URYSjDUUry_3Bg7h3jj92fEcDfouQ";

if (!empty($data->content) && !empty($data->goip_line)  && !empty($data->from_number)) {

    $get_sim = $data->goip_line;
    $sim_id = mb_substr($get_sim, -2);
    $gateway = mb_substr($get_sim, 0, -2);

    date_default_timezone_set('Europe/Moscow');
    $now = date("Y-m-d H:i");

    // парсим название шлюза и сим из файла настроек
    $gateway_name = array_key_exists($gateway, $settings) ? $settings[$gateway] : $gateway;
    $sim_name = array_key_exists($gateway . '_' . $sim_id, $settings) ? $settings[$gateway . '_' . $sim_id] : $sim_id;


    $send = "&#128233 <b>Входящая SMS</b>\r\nна шлюз: " . $gateway_name . "\r\nсим: " . $sim_name . "\r\nот: " . $data->from_number . "\r\n\r\n" . $data->content . "\r\n\r\n" . $now;

    if (!empty($settings[$data->goip_line])) {
        $numbers = $settings[$data->goip_line];

        if (strpos($numbers, ',')) {
            $numbers = explode(',', $numbers);
        }

        if (is_array($numbers)) {
            foreach ($numbers as $number) {
                SendSIMPLEmsg($number, $data->from_number, $send);
            }
        } else {
            SendSIMPLEmsg($numbers, $data->from_number, $send);
        }
    }
}


function SendSIMPLEmsg($To, $From, $Body) {

    $oSocket = fsockopen('localhost', 5038, $errnum, $errdesc) or die("Connection to host failed");
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
