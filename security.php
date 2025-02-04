<?php
session_start();

$rate_limit = 100;
$log_file = 'access.log';
$block_duration = 600;

$ip_address = hash('sha256', $_SERVER['REMOTE_ADDR']);
$current_time = time();

function getLogs($file) {
    if (!file_exists($file)) {
        return [];
    }
    $data = file_get_contents($file);
    return json_decode($data, true) ?: [];
}

function saveLogs($file, $logs) {
    $data = json_encode($logs);
    file_put_contents($file, $data, LOCK_EX);
}

$logs = getLogs($log_file);

foreach ($logs as $ip => $data) {
    if ($data['timestamp'] < ($current_time - $block_duration)) {
        unset($logs[$ip]);
    }
}

if (isset($logs[$ip_address])) {
    $logs[$ip_address]['count']++;
    if ($logs[$ip_address]['count'] > $rate_limit) {
        header("HTTP/1.1 429 Too Many Requests");
        die('Rate limit exceeded. Please try again later.');
    }
} else {
    $logs[$ip_address] = ['count' => 1, 'timestamp' => $current_time];
}

$logs[$ip_address]['timestamp'] = $current_time;
saveLogs($log_file, $logs);
