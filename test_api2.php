<?php
$url = 'https://eventregistry.org/api/v1/article/getArticles';
$apiKey = 'a7e33fa2-2055-4ec9-a31c-b7f3f551d984';
$payload = [
    'apiKey' => $apiKey,
    'action' => 'getArticles',
    'dateStart' => '2026-07-10',
    'dateEnd' => '2026-07-10',
    'articlesCount' => 2
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res = curl_exec($ch);
echo "FUTURE DATE NO KEYWORD:\n" . substr($res, 0, 500) . "\n\n";

$payload['keyword'] = 'American Airlines';
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res2 = curl_exec($ch);
echo "FUTURE DATE WITH KEYWORD:\n" . substr($res2, 0, 500) . "\n\n";

$payload = [
    'apiKey' => $apiKey,
    'action' => 'getArticles',
    'dateStart' => '2024-05-10',
    'dateEnd' => '2024-05-10',
    'articlesCount' => 2
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res3 = curl_exec($ch);
echo "PAST DATE NO KEYWORD:\n" . substr($res3, 0, 500) . "\n\n";
