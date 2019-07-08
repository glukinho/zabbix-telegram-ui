<?php

require __DIR__ . '/vendor/autoload.php';

include 'settings.php';
include 'func.php';

$commands_paths = [
    __DIR__ . '/Commands',
];

	
$z = new \ZabbixApi\ZabbixApi($zabbix_api_url, $zabbix_user, $zabbix_pass);


try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
	
	Longman\TelegramBot\TelegramLog::initUpdateLog($tg_update_log_path);
	Longman\TelegramBot\TelegramLog::initDebugLog($tg_debug_log_path);
	Longman\TelegramBot\TelegramLog::initErrorLog($tg_error_log_path);

	$telegram->enableAdmins($tg_users);

	$telegram->addCommandsPaths($commands_paths);

    // Handle telegram webhook request
    $telegram->handle();
	
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    echo $e->getMessage();
}