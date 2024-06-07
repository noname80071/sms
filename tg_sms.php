<?php

$input = file_get_contents('php://input');
$data = json_decode($input);

// chat_id получателей
$settings = parse_ini_file("tg-settings.ini");

// Токен для бота
$token = "";
if (!empty($data->content) && !empty($data->goip_line)  && !empty($data->from_number)) {

    $get_sim = $data->goip_line;
    $sim_id = mb_substr($get_sim, -2);
    $gateway = mb_substr($get_sim, 0, -2);
    $content = $data->content;

    date_default_timezone_set('Europe/Moscow');
    $now = date("Y-m-d H:i");

    // парсим название шлюза и сим из файла настроек
    $gateway_name = array_key_exists($gateway, $settings) ? $settings[$gateway] : $gateway;
    $sim_name = array_key_exists($gateway . '_' . $sim_id, $settings) ? $settings[$gateway . '_' . $sim_id] : $sim_id;

    if (!empty(strpos($content, "codigo: "))) {
        $sms_index = strpos($content, "codigo: ");
        // Индекс начала кода
        $sms_index += strlen("codigo: ");
        // Определяем индекс конца кода
        $end_index = strpos($content, ".", $sms_index);
        if (empty($end_index)) {
            $end_index = strlen($content);
        }
        // Извлекаем код из сообщения
        $sms_code = substr($content, $sms_index, $end_index - $sms_index);

    } else if (!empty(strpos($content, "codice "))){
        $sms_index = strpos($content, "codice ", 30);
        $sms_index += strlen('codice ');
        $end_index = strpos($content, " ", $sms_index);
        if (empty($end_index)) {
            $end_index = strlen($content);
        }
        $sms_code = substr($content, $sms_index, $end_index - $sms_index);
    } else{
        if (preg_match('/\b([a-zA-Z]*[0-9]+[a-zA-Z0-9]*)\b/u', $content, $matches)) {
            $sms_code = $matches[1];
        }
    }

    $send = "&#128233 <b>Входящая SMS</b>\r\nна шлюз: " . $gateway_name . "\r\nсим: " . $sim_name . "\r\nорт: " .
        $data->from_number . "\r\n\r\n" . $content . "\r\n\r\n" . $now . "\r\n\r\n<b>Найденный код: </b>" . $sms_code;

    $tg_send = urlencode($send);
    $tg_send = str_replace('%3Cbr%3E', '%0A', $tg_send);
    $tg_send = str_replace('%3Chr%3E', '%0A', $tg_send);
    $tg_send = str_replace('%26laquo%3B', '', $tg_send);
    $tg_send = str_replace('%26raquo%3B', '', $tg_send);
    $tg_send = str_replace('%26ndash%3B', ' ', $tg_send);
    $tg_send = str_replace('%26quot%3B', ' ', $tg_send);
    $tg_send = str_replace('%26nbsp%3B', ' ', $tg_send);
    $tg_send = str_replace('%3C%23%3E', ' ', $tg_send);
    if (!empty($settings[$data->goip_line])) {
        $chat_id = $settings[$data->goip_line];
        if (strpos($chat_id, ',')) {
            $chat_id = explode(',', $chat_id);
        }
        if (is_array($chat_id)) {
            foreach ($chat_id as $id) {
                $sendToTelegram = get("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&parse_mode=html&text=" . trim($tg_send));
            }
        } else {
            print_r(trim($tg_send));
            $sendToTelegram = get("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id . "&parse_mode=html&text=" . trim($tg_send));
        }
    }
}

function get($url)
{
    $ch = curl_init($url);
    curl_setopt(
        $ch,
        CURLOPT_USERAGENT,
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.4) Gecko/2008102920 AdCentriaIM/1.7 Firefox/3.0.4"
    );
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    return $result;
}
