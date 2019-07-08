<?php

function process_value($value, $unit, $rules) {
	// return object with new value and new unit
	
	$result = (object)[ 'value' => $value, 'unit' => $unit ];
	
	if (isset($rules->$unit)) {
		$action = $rules->$unit->action;
		
		switch ($action) {
			case 'divide':
				// take divider that gives lowest value over than 1.00
				
				$results = array();
				foreach ($rules->$unit->dividers as $d_name => $d_value) {
					$division_result = $value / $d_value;
					if ($division_result > 1) $results[$d_name] = $division_result;
				}
				$new_value = min($results);
				$new_unit = array_search($new_value, $results);
				
				if (isset($rules->$unit->round)) $new_value = round($new_value, $rules->$unit->round);
				
				$result->value = $new_value;
				$result->unit = $new_unit;
				
				break;
				
				
			case "round":
				$result->value = round($value, $rules->$unit->round);
				$result->unit = $unit;
				
				break;
				
				
			case 'timestamp':
				$dt1 = new DateTime();
				$dt2 = new DateTime("@" . round($value));
				$ago = $dt1->diff($dt2)->format($rules->$unit->format_ago);

				$result->value = date($rules->$unit->format, $value) . " ($ago ago)";
				$result->unit = false;
				break;
				
				
			case 'time_interval':
				if ($value < 1) {
					// to avoid processing low values like 0.0002s (0.2ms)
					$result->value = $value;
					$result->unit = $unit;
				} else {
					$dt1 = new DateTime("@0");
					$dt2 = new DateTime("@$value");
					
					$result->value = $dt1->diff($dt2)->format($rules->$unit->format);
					$result->unit = false;
				}
				break;
				
				
			default: 
				break;
		}
	}
	
	return $result;
}


// converts $1, $2...$9 in item names to actual ones according to item key inside square brackets
// example:
// item name: "example name $1 of $2", key: "example.key[abc,def]"
// result name will be: "example name abc of def"

function process_item_name($item_name, $item_key) {
	$result = (object)[ 'name' => $item_name ];
	
	if (preg_match('/\$[1-9]/', $item_name)) {
		preg_match_all("/\[([^\]]*)\]/", $item_key, $key_parts);
		print_r($key_parts);
		$key_parts_arr = explode(',', $key_parts[1][0]);		
		
		$item_name_new = $item_name;
		for ($i = 1; $i <= 9; $i++) {
			if (array_key_exists($i-1, $key_parts_arr)) $item_name_new = str_replace('$'.$i, $key_parts_arr[$i-1], $item_name_new);
		}
		
		$result->name = $item_name_new;
	} 
	
	return $result;

}


function get_graph_latest_data($itemid, $period, $zabbix_version = '3.4.3') {
	global $zabbix_user, $zabbix_pass, $zabbix_auth_url, $zabbix_graph_url_template;
	
	$graph_url = $zabbix_graph_url[$zabbix_version];
	
	// Get the login-cookie 
	$output = array();
	
	$strWgetLogin = "wget --save-cookies=cookies.txt --keep-session-cookies --post-data \"name={$zabbix_user}&password={$zabbix_pass}&enter=Sign+in\" -O - -q \"$zabbix_auth_url\"";
	// echo $strWgetLogin . PHP_EOL;
	exec($strWgetLogin, $output);
	
	// print_r($output);
	// echo PHP_EOL;
	
	$url = $zabbix_graph_url_template[$zabbix_version];
	$url = str_replace('%PERIOD%', $period, $url);
	$url = str_replace('%ITEMID%', $itemid, $url);
	
	// echo $url;
	// echo PHP_EOL;
	
	// Wget our graph! 
	$graph_filename = 'graphs/graph_'.uniqid().'.png';
	$strWget = "wget --load-cookies=cookies.txt -O $graph_filename -q \"$url\""; 
	// echo $strWget . PHP_EOL;
	exec($strWget, $output); 
	
	// print_r($output);
	// echo PHP_EOL;
	
	if (file_exists($graph_filename)) {
		return realpath($graph_filename);
	} else {
		return false;
	}
}


// returns number of seconds, eg '2m' => 120, '3h' => 10800
function delay_to_seconds($delay) {
	global $delays_processing_rules;
	
	$result = $delay;
	
	foreach ($delays_processing_rules as $key => $value) {
		if (strpos($delay, $key) !== false) {
			
			$d = str_replace($key, $value, $delay);
			
			$i = DateInterval::createFromDateString($d);
			
			$result = 	$i->y * 365 * 24 * 60 * 60 + 
						$i->m * 30 * 24 * 60 * 60 + 
						$i->d * 24 * 60 * 60 + 
						$i->h * 60 * 60 + 
						$i->i * 60 + 
						$i->s;
			
			break;
		}
	}
	
	return $result;
}


function epoch_to_str($epoch) {
	global $values_processing_rules;
	return (new DateTime("@".$epoch))->format($values_processing_rules->unixtime->format);
}

function ago($epoch) {
	global $values_processing_rules;
	return (new DateTime())->diff(new DateTime("@".$epoch))->format($values_processing_rules->unixtime->format_ago);
}






