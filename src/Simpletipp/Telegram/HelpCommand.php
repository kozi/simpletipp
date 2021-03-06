<?php

namespace Simpletipp\Telegram;

class HelpCommand extends TelegramCommand
{

    protected function handle()
    {
        $this->sendText(self::helpMessage());
    }

    public static function helpMessage()
    {
        $message = "Die folgenden Kommandos stehen zur Verfügung\n\n"
            . "*H* /h Zeige die Tabelle (*hd* /hd Details)\n"
            . "*S* /s Zeige die aktuellen Spiele (*sn* /sn Nächster)\n"
            . "*T* /t Tipps abgeben\n"
            . "*C* /c Zeige ein zufälliges Zitat\n"
            . "*Z* /z Lade die aktuelle Zeigler-Folge\n"
            . "Die Kommandos um die Daten der anderen Tipper zu sehen folgen noch!";
        return $message;
    }

}
