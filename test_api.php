<?php
$url = 'https://eventregistry.org/api/v1/article/getArticles';
$apiKey = 'a7e33fa2-2055-4ec9-a31c-b7f3f551d984';
$payload = [
    'apiKey' => $apiKey,
    'action' => 'getArticles',
    'dateStart' => '2022-07-03',
    'dateEnd' => '2022-07-04',
    'articlesCount' => 2
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res = curl_exec($ch);
echo "NO TIME:\n" . substr($res, 0, 500) . "\n\n";

$payload['timeStart'] = '15:00:00';
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res2 = curl_exec($ch);
echo "WITH TIMESTART:\n" . substr($res2, 0, 500) . "\n\n";

$payload = [
    'apiKey' => $apiKey,
    'action' => 'getArticles',
    'dateStart' => '2022-07-03T15:00:00Z',
    'dateEnd' => '2022-07-04T15:00:00Z',
    'articlesCount' => 2
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res3 = curl_exec($ch);
echo "COMBINED FORMAT:\n" . substr($res3, 0, 500) . "\n\n";
