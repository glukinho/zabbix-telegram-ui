<?php

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\InlineKeyboardMarkup;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboardButton;


class ProblemsCommand extends AdminCommand
{
	protected $name = 'problems';                      // Your command's name
    protected $description = 'Last problems'; 	// Your command description
    protected $usage = '/problems';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command
	
		
	public function execute()
    {
		global $z, $search_results_columns, $problems_interval, $trigger_priorities;
		
        $message = $this->getMessage();            // Get Message object

		$search_string = str_replace('/' . $message->getCommand() . ' ', '', $message->getText());
		
		try
		{
			$now = time();
			$problems = $z->problemGet( [ 
				'time_from' => $now - $problems_interval->value, 
				'time_till' => $now,
				'sortfield' => 'eventid',
				'sortorder' => 'asc'
			] );
		
			$keyb = [
				'inline_keyboard' => [],
			];
			$result[] = "problems for last {$problems_interval->name}";
			$result[] = "---";

			foreach ($problems as $p) {
				$t = $z->triggerGet( [ 'triggerids' => $p->objectid, 'selectHosts' => 'expand' ] )[0];
				$hostid = $t->hosts[0]->hostid;
				$h = $z->hostGet( [ 'hostids' => $hostid ] )[0];
				
				$prio = strtoupper($trigger_priorities[$t->priority]);
				
				$result[] = "• " . epoch_to_str($p->clock) . " (" . ago($p->clock) . " ago) - {$prio} - {$h->name} - {$t->description}";
			}
			$keyb['inline_keyboard'][] = [ new InlineKeyboardButton( [ 'text' => "test", 'callback_data' => $this->getTelegram()->getBotName() ] ) ];
		}
		catch(Exception $e)
		{
			// Exception in ZabbixApi catched
			echo $e->getMessage();
		}
		

        $chat_id = $message->getChat()->getId();

        $data = [
            'chat_id' => $chat_id,
            'text'    =>  implode(PHP_EOL, $result),
			'reply_markup' =>  new InlineKeyboardMarkup($keyb),
        ];

        return Request::sendMessage($data);        // Send message!
    }
}