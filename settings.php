<?php

$zabbix_base_url	= 'url';	// http://zabbix.nastroim.ru:81/zabbix/
$zabbix_user		= 'user';
$zabbix_pass		= 'pass';

$bot_api_key  				= '<BOT_KEY>';
$bot_username 				= '<BOT_NAME>';

// telegram user ids who is allowed to access bot functions
// integer values
$tg_users = [
	// 123456789,
	// 987654321,
];



$zabbix_api_url 				= $zabbix_base_url . 'api_jsonrpc.php';
$zabbix_auth_url				= $zabbix_base_url . 'index.php?login=1';

$zabbix_graph_url_template = [
	'3.4.3' => $zabbix_base_url . 'chart.php?period=%PERIOD%&isNow=1&itemids[]=%ITEMID%&width=1000',
	'4.2.0' => $zabbix_base_url . 'chart.php?from=now-%PERIOD%&to=now&itemids[]=%ITEMID%&width=1000&profileIdx=web.item.graph.filter'
];




$values_processing_rules = (object)[
	'%' => (object)[
		'action' => 'round',
		'round' => 1
	],
	
	'Bps' => (object)[
		'action' => 'divide',
		'dividers' => [
			'KBps' => 1024,
			'MBps' => 1024 * 1024,
			'GBps' => 1024 * 1024 * 1024
		],
		'round' => 1
	],
	
	'bps' => (object)[
		'action' => 'divide',
		'dividers' => [
			'Kbps' => 1024,
			'Mbps' => 1024 * 1024,
			'Gbps' => 1024 * 1024 * 1024
		],
		'round' => 1
	],
	
	'B' => (object)[
		'action' => 'divide',
		'dividers' => [
			'KB' => 1024,
			'MB' => 1024 * 1024,
			'GB' => 1024 * 1024 * 1024,
			'TB' => 1024 * 1024 * 1024 * 1024
		],
		'round' => 1
	],
	
	'unixtime' => (object)[
		'action' => 'timestamp',
		'format' => 'Y-m-d H:i:s',
		'format_ago' => '%ad %hh %im %ss'	// 11d 4h 50m 36s
	],
	
	'uptime' => (object)[
		'action' => 'time_interval',
		'format' => '%ad %hh %im %ss'	// 11d 04h 50m 36s
	],
	
	's' => (object)[
		'action' => 'time_interval',
		'format' => '%ad %hh %im %ss'	// 11d 04h 50m 36s
	]
];


$graph_intervals = [
	'10m'	=> 10 * 60,
	'1h'	=> 3600,
	'3h'	=> 3 * 3600,
	'24h'	=> 24 * 3600,
	'7d'	=> 7 * 24 * 3600,
	'30d'	=> 30 * 24 * 3600
];


$search_results_columns	= 3;
$applications_columns	= 3;


$delays_processing_rules = [
	'ms' => false,
	's' => ' seconds',
	'm' => ' minutes',
	'h' => ' hours',
	'd' => ' days',
	'w' => ' weeks'
];


$problems_interval = (object)[
	'name' => '30 days',
	'value' => 24 * 3600 * 30
];


$trigger_priorities = [
	0 => 'unknown priority',
	1 => 'information',
	2 => 'warning',
	3 => 'average',
	4 => 'high',
	5 => 'disaster'
];

$tg_update_log_path = 'logs/tg-update.log';
$tg_debug_log_path = 'logs/tg-debug.log';
$tg_error_log_path = 'logs/tg-error.log';






