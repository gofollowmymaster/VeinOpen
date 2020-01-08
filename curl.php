<?php

$curl = curl_init();

curl_setopt_array($curl, [CURLOPT_URL            => "https://oapi.dingtalk.com/robot/send?access_token=6075a0e7b2ff4bba5cced829d44c65b394978b78aaef5d28b5598127b5a19713",
                          CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT        => 1, CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST",
                          CURLOPT_POSTFIELDS     => "{\n    \"msgtype\": \"text\",\n    \"text\": {\n        \"content\": \"伙伴sd\"\n    },\n    \"at\": {\n        \"atMobiles\": [],\n        \"isAtAll\": false\n    }\n}",
                          CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],]);

list($content, $status,$error) = [curl_exec($curl), curl_getinfo($curl),curl_error($curl) , curl_close($curl)];
$httpCode = intval($status["http_code"]);
if ($httpCode !== 200) {
    echo('HTTP请求状态=' . $httpCode . '响应信息=' . (is_array($content) ? json_encode($content) :
            $content) . ':状态详情' . json_encode($status).':错误详情'.$error);
} else {
    echo 'success:' . $content;
}
