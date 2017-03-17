<?php
#    _    _   _       _                                   _      _____                 _    
#   | |  | | (_)     | |              /\                 | |    / ____|               | |   
#   | |__| |  _    __| |   ___       /  \     _ __     __| |   | (___     ___    ___  | | __
#   |  __  | | |  / _` |  / _ \     / /\ \   | '_ \   / _` |    \___ \   / _ \  / _ \ | |/ /
#   | |  | | | | | (_| | |  __/    / ____ \  | | | | | (_| |    ____) | |  __/ |  __/ |   < 
#   |_|  |_| |_|  \__,_|  \___|   /_/    \_\ |_| |_|  \__,_|   |_____/   \___|  \___| |_|\_\
#                                                                                           
#                                                                                           
# The original minigame, free, and better than ever !
# @author Ad5001
# @link ad5001.eu

namespace Ad5001\HideAndSeek\tasks;

use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

use Ad5001\HideAndSeek\Main;
use Ad5001\HideAndSeek\Game;



class SignsTask extends PluginTask {


   public function __construct(Main $main) {
        parent::__construct($main);
        $this->main = $main;
        $this->server = $main->getServer();
    }


   public function onRun($tick) {
       foreach($this->server->getLevels() as $lvl) {
           foreach($lvl->getTiles() as $t) {
               if($t instanceof Sign) {
                   if(isset($t->namedtag->hideAndSeekSignData)) {
                       if(!isset($t->namedtag->hideAndSeekSignData->model)) {
                           $this->getOwner()->getLogger()->debug("SettingModel l44");
                           $t->namedtag->hideAndSeekSignData->setValue([
                               "model" => new ListTag("model", [
                                   1 => $t->namedtag->Text1,
                                   2 => $t->namedtag->Text2,
                                   3 => $t->namedtag->Text3,
                                   4 => $t->namedtag->Text4
                                ]),
                                "levelName" => $t->namedtag->hideAndSeekSignData->level ?? $t->namedtag->hideAndSeekSignData->levelName,
                                "level" => $t->namedtag->hideAndSeekSignData->level ?? $t->namedtag->hideAndSeekSignData->levelName // Sometimes, there is no way to know why things like that works...
                           ]);
                       }
                       $line1 = $this->parse((string) $t->namedtag->hideAndSeekSignData->model[1], $t);
                       $line2 = $this->parse((string) $t->namedtag->hideAndSeekSignData->model[2], $t);
                       $line3 = $this->parse((string) $t->namedtag->hideAndSeekSignData->model[3], $t);
                       $line4 = $this->parse((string) $t->namedtag->hideAndSeekSignData->model[4], $t);
                       $t->setText($line1, $line2, $line3, $line4);
                   }
               }
           }
       }
    }


    /*
    Parses the text of the sign
    @param     $string    string
    @param     $sign    \pocketmine\tile\Sign
    @return string
    */
    public function parse(string $string, Sign $sign) : string {
        safe_var_dump($sign->namedtag->hideAndSeekSignData->getValue());
        $game = $this->main->getGameManager()->getGameByName($sign->namedtag->hideAndSeekSignData->levelName ?? $sign->namedtag->hideAndSeekSignData->level);
        $str = str_ireplace("{world}", $sign->namedtag->hideAndSeekSignData->offsetGet("levelName"), $string);
        $str = str_ireplace("{maxp}", $game->getMaxPlayers(), $str);
        $str = str_ireplace("{pls}", count($game->getPlayers()), $str);
        $str = str_ireplace("{step}", $game->step, $str);
        $str = str_ireplace("{playing}", ($game->step == Game::STEP_WAIT || $game->step == Game::STEP_START) ? "Waiting" : "Starting", $str);
        return $str;
    }


}