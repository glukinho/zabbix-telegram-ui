<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\InlineKeyboardMarkup;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

/**
 * Callback query command
 */
class CallbackqueryCommand extends SystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'callbackquery';
    protected $description = 'Reply to callback query';
    protected $version = '1.0.0';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
		global $z, $values_processing_rules, $graph_intervals, $search_results_columns, $applications_columns;
		
        $update = $this->getUpdate();
        $callback_query = $update->getCallbackQuery();
        $callback_query_id = $callback_query->getId();
        $callback_data = $callback_query->getData();

        $data['callback_query_id'] = $callback_query_id;
		
		$command = explode(' ', $callback_data);

        if ($callback_data == '/test') {
			$data['callback_query_id'] = $callback_query_id;
            $data['text'] = $callback_data;
            $data['show_alert'] = false;
			
			Request::answerCallbackQuery( $data );
			
			
			$this->getTelegram()->executeCommand('problems');
			

		} elseif ($command[0] == '/hostapps') {
			if (!$this->getTelegram()->isAdmin()) return Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id, 'text' => 'ERROR: no auth' ] );
			
			Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id ] );
			
			$hostid = $command[1];
			$host = $z->hostGet( [ 'hostids' => $hostid ] )[0];
			$interface = $z->hostinterfaceGet( [ 'hostids' => $hostid, 'main' => 1 ] )[0];
			$applications = $z->applicationGet( ['hostids' => $hostid ] );
			
			$info[] = "Host: {$host->name}";
			$info[] = "IP:  {$interface->ip}";
			
			$keyb = [
				'inline_keyboard' => []
			];
			$result = [];
			
			$i = 0;
			foreach ($applications as $a) {
				$result[] = $a->name;
				$button = new InlineKeyboardButton( [ 'text' => $a->name, 'callback_data' => '/items ' . $hostid . ' ' . $a->applicationid ] );
				$keyb['inline_keyboard'][floor($i / $applications_columns)][] = $button; // buttons in $applications_columns columns
								
				$i++;
			}
			// $keyb['inline_keyboard'][] = [ new InlineKeyboardButton( [ 'text' => "new search", 'callback_data' => "/search srv" ] ) ];
			$keyb['inline_keyboard'][] = [ new InlineKeyboardButton( [ 'text' => "Triggers", 'callback_data' => "/triggers $hostid" ] ) ];
			$data = [
				'chat_id' => $callback_query->getMessage()->getChat()->getId(),
				'text'    => implode(PHP_EOL, $info) . PHP_EOL . '---' . PHP_EOL . 'select application:',
				'reply_markup' =>  new InlineKeyboardMarkup($keyb)
			];
			return Request::sendMessage($data);
		
		
		} elseif ($command[0] == '/items') {
			if (!$this->getTelegram()->isAdmin()) return Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id, 'text' => 'ERROR: no auth' ] );
			
			Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id ] );
			
			$hostid = $command[1];
			$applicationid = $command[2];
			
			$items = $z->itemGet( ['hostids' => $hostid, 'applicationids' => $applicationid, 'status' => 0] );
			
			foreach ($items as $i) {				
				$display_name = process_item_name($i->name, $i->key_)->name;
				
				$value = $i->lastvalue;
				$processed_value = process_value($value, $i->units, $values_processing_rules);
				$display_value = $processed_value->value;
				$display_unit = $processed_value->unit;
				
				$lastclock = new \DateTime("@" . $i->lastclock);
				$now = new \DateTime();
				$planned_delay_sec = delay_to_seconds($i->delay);
				$actual_delay_sec = time() - $i->lastclock;
				$actual_delay = $now->diff($lastclock)->format($values_processing_rules->unixtime->format_ago);
				$delay_str = ($actual_delay_sec > $planned_delay_sec * 2 ? " - delayed for " . $actual_delay . "!" : "");
				
				
				if ($i->valuemapid <> 0) {
					$valuemap = $z->valuemapGet( [ 'valuemapids' => $i->valuemapid, 'selectMappings' => 'extend' ] );
					
					 
					foreach ($valuemap[0]->mappings as $m) {
						if ($m->value == $value) {
							$display_value = "{$m->newvalue} ($value)";
							break;
						}
					} 
				}
			
				// $result[] = "{$i->name} ($match[1]): {$display_value} {$i->units}";
				$result[] = "{$display_name}: {$display_value}" . ($display_unit ? " $display_unit" : "") . $delay_str . ($i->state == 1 ? " - not supported!" : "");
			}
			$keyb['inline_keyboard'][] = [ 
				new InlineKeyboardButton( [ 'text' => "back to host", 	'callback_data' => "/hostapps {$hostid}" ] ),
				new InlineKeyboardButton( [ 'text' => "reload", 		'callback_data' => $callback_data ] ), 
				new InlineKeyboardButton( [ 'text' => "graphs", 		'callback_data' => "/graphitems {$hostid} {$applicationid}" ] )
			];
			// $keyb['inline_keyboard'][] = [  ];
			// $keyb['inline_keyboard'][] = [ new InlineKeyboardButton( [ 'text' => "new search", 'switch_inline_query_current_chat' => "/search " ] ) ];
			$data = [
				'chat_id' => $callback_query->getMessage()->getChat()->getId(),
				'text'    => implode(PHP_EOL, $result),
				'reply_markup' =>  new InlineKeyboardMarkup($keyb)
			];
			return Request::sendMessage($data);
		
		
		} elseif ($command[0] == '/graphitems') {
			if (!$this->getTelegram()->isAdmin()) return Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id, 'text' => 'ERROR: no auth' ] );
			
			Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id ] );
			
			$hostid = $command[1];
			$applicationid = $command[2];
			$items = $z->itemGet( ['hostids' => $hostid, 'applicationids' => $applicationid, 'status' => 0] );
			
			foreach ($items as $i) {				
				$display_name = process_item_name($i->name, $i->key_)->name;
				
				$keyb['inline_keyboard'][] = [ new InlineKeyboardButton( [ 'text' => $display_name, 'callback_data' => "/graphinterval {$i->itemid}" ] ) ];
			}
			$data = [
				'chat_id' => $callback_query->getMessage()->getChat()->getId(),
				'text'    => "select item for graph:",
				'reply_markup' =>  new InlineKeyboardMarkup($keyb)
			];
			return Request::sendMessage($data);
		

		} elseif ($command[0] == '/graphinterval') {
			Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id ] );
			
			$itemid = $command[1];
			$buttons = [];
			
			foreach ($graph_intervals as $key => $value) {
				$buttons[] = new InlineKeyboardButton( [ 'text' => $key, 'callback_data' => "/graph {$itemid} $value" ] );
			}
			$keyb['inline_keyboard'][] = $buttons;
			$data = [
				'chat_id' => $callback_query->getMessage()->getChat()->getId(),
				'text'    => "select interval, last ...",
				'reply_markup' =>  new InlineKeyboardMarkup($keyb)
			];
			return Request::sendMessage($data);
		
		
		} elseif ($command[0] == '/graph') {
			if (!$this->getTelegram()->isAdmin()) return Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id, 'text' => 'ERROR: no auth' ] );
			
			Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id ] );
			
			$itemid = $command[1];
			$period = $command[2];
			$zabbix_version = $z->apiinfoVersion();
			
			$file = get_graph_latest_data($itemid, $period, $zabbix_version);
			
			$data = [
				'chat_id' => $callback_query->getMessage()->getChat()->getId(),
				'photo'   => Request::encodeFile($file)
			];
			return Request::sendPhoto($data);
		
        } else {
            return Request::answerCallbackQuery( [ 'callback_query_id' => $callback_query_id, 'text' => "ERROR: unknown callback $callback_data" ] );
        }

        // return Request::answerCallbackQuery($data);
    }
}
