<?php

namespace Simpletipp\Modules;

use Contao\Input;
use Contao\MemberModel;
use Simpletipp\SimpletippModule;
use Simpletipp\Telegram\HelpCommand;
use Simpletipp\Telegram\HighscoreCommand;
use Simpletipp\Telegram\MatchesCommand;
use Simpletipp\Telegram\StartCommand;
use Simpletipp\Telegram\TippCommand;
use Simpletipp\Telegram\ZeiglerCommand;
use Simpletipp\Telegram\ZitatCommand;
use Telegram\Bot\Api;

/**
 * Class SimpletippTelegram
 *
 * @copyright  Martin Kozianka 2014-2019
 * @author     Martin Kozianka <martin@kozianka.de>
 * @package    Controller
 */
class SimpletippTelegram extends SimpletippModule
{
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $this->Template = new \BackendTemplate('be_wildcard');
            $this->Template->wildcard = '### SimpletippTelegram ###';
            return $this->Template->parse();
        }
        if ($this->simpletipp->telegram_url_token !== Input::get('token')) {
            die('Missing token');
        }
        $this->strTemplate = $this->simpletipp_template;
        return parent::generate();
    }

    protected function compile()
    {
        $telegram = new Api($this->simpletipp->telegram_bot_key);
        $update = ($telegram !== null) ? $telegram->getWebhookUpdates() : null;
        $message = ($update !== null) ? $update->getMessage() : null;
        $text = ($message !== null) ? strtolower($message->getText()) : null;
        $chat_id = ($message !== null) ? $message->getChat()->getId() : null;
        $chatMember = ($chat_id !== null) ? MemberModel::findOneBy('telegram_chat_id', $chat_id) : null;

        if (is_string($text) && strpos($text, "/start") === 0) {
            // Handle start command
            $command = new StartCommand($telegram, $this, $message, $chatMember);
            $command->handleCommand();
            exit;
        } elseif ($chatMember === null) {
            if ($chat_id !== null) {
                $telegram->sendMessage(['text' => 'Chat not registered.', 'chat_id' => $chat_id]);
            }
            exit;
        }

        // Handle all other commands
        switch ($text) {
            case "/hilfe":
            case "hilfe":
            case "/help":
            case "help":
            case "?":
                $command = new HelpCommand($telegram, $this, $message, $chatMember);
                break;
            case "/h":
            case "h":
            case "/hd":
            case "hd":
                $command = new HighscoreCommand($telegram, $this, $message, $chatMember);
                $command->enableDetails(in_array($text, ["hd", "/hd"]));
                break;
            case "/t":
            case "t":
                $command = new TippCommand($telegram, $this, $message, $chatMember);
                $command->isInitial(true);
                break;
            case "/s":
            case "s":
            case "/sn":
            case "sn":
                $command = new MatchesCommand($telegram, $this, $message, $chatMember);
                $command->setNext(in_array($text, ["sn", "/sn"]));
                break;
            case "/z":
            case "z":
                $command = new ZeiglerCommand($telegram, $this, $message, $chatMember);
                break;
            case "/c":
            case "c":
                $command = new ZitatCommand($telegram, $this, $message, $chatMember);
                break;
            default:
                $command = new TippCommand($telegram, $this, $message, $chatMember);
        }

        $command->handleCommand();
        exit;
    }
}
