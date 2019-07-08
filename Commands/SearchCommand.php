<?php

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\InlineKeyboardMarkup;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboardButton;


class SearchCommand extends AdminCommand
{
	protected $name = 'search';                      // Your command's name
    protected $description = 'Search for hosts'; 	// Your command description
    protected $usage = '/search';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command
	
		
	public function execute()
    {
		global $z, $search_results_columns;
		
		// file_put_contents('tmp.txt', print_r($this->getConfig(), true), FILE_APPEND);
		
        $message = $this->getMessage();            // Get Message object
		
		$search_string = str_replace('/' . $message->getCommand() . ' ', '', $message->getText());
		
		try
		{
			$hosts = $z->hostGet( [ 'search' => [ 'name' => $search_string ], 'filter' => [ 'status' => 0 ] ] );
		
			$keyb = [
				'inline_keyboard' => [],
			];
			$result = [];
			$i = 0;
			foreach ($hosts as $h) {
				$result[] = $h->name;
				$button = new InlineKeyboardButton( [ 'text' => $h->name, 'callback_data' => '/hostapps ' . $h->hostid ] );
				$keyb['inline_keyboard'][floor($i / $search_results_columns)][] = $button; // buttons in $search_results_columns columns
				$i++;
			}
		}
		catch(Exception $e)
		{
			// Exception in ZabbixApi catched
			echo $e->getMessage();
		}
		

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text'    => "search results:", 	// Set message to send
			'reply_markup' => new InlineKeyboardMarkup($keyb)
			// 'reply_markup' => new ReplyKeyboardMarkup($keyb)
        ];

        return Request::sendMessage($data);        // Send message!
    }
}