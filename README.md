# zabbix-tg-ui
Zabbix UI based on Telegram bot

## Information
This telegram bot provides some basic information from Zabbix:
 - hosts search
 - items and latest data
 - graphs based on latest data
 - recent problems

This bot can replace Zabbix web frontend in cases when you want to see some info quickly on your smartphone, no need to open desktop browser.

Some GIF animated demonstration here: https://gifyu.com/image/EQdk

## How it works
PhpZabbixApi library communicates with Zabbix API.

Graphs are retrieved from Zabbix using wget with some URL (emulating user entering web interface) as Zabbix API does not provide ability of getting latest data based graphs.

php-telegram-bot library communicates with Telegram API.

## Dependencies
- https://github.com/confirm/PhpZabbixApi
- https://github.com/php-telegram-bot/core

## Requirements
- bot itself needs:
  - PHP (tested on 5.6.40)
  - php-mbstring
  - wget
- Zabbix Server (tested on 3.4.3 and 4.2.0) with access to API URL from where your bot is hosted

## Installation
1. Create your telegram bot with BotFather, setup a webhook URL to your server: `https://your-bot.example.com/bot.php`
1. Unpack files to your server
1. Install Composer and project dependencies: `php composer.phar require`
1. Replace some files with ones in `custom` folder:

   `CallbackqueryCommand.php` => `vendor/longman/telegram-bot/src/Commands/SystemCommands/CallbackqueryCommand.php`
   `ZabbixApi.class.php` => `vendor/confirm-it-solutions/php-zabbix-api/build/ZabbixApi.class.php`
   `ZabbixApiAbstract.class.php` => `vendor/confirm-it-solutions/php-zabbix-api/build/ZabbixApiAbstract.class.php`
   
1. Adjust `settings.php` to your environment:
   - Zabbix URL, user and password
   - bot token and name
   - IDs of Telegram users who are allowed to use bot. You can get Telegram ID easily by sending `/whoami` command to the bot.
   
1. For making graphs, you may need to adjust `$zabbix_graph_url_template` in `settings.php` according to your Zabbix version. Graph URLs for 3.4.3 and 4.2.0 versions are already tested.

## How to use
- To search, type `/search <something>` and you will get hosts having `<something>` in visible names. Then use inline buttons to get items, latest data and graphs.
- To get list of recent problems, use `/problems` command.
- See `settings.php` to adjust bot's behavior.
