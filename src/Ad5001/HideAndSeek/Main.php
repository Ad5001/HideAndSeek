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

namespace Ad5001\HideAndSeek;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
class Main extends PluginBase implements Listener {

    const PREFIX = "§a§l§o[§r§l§2Hide§eAnd§6Seek§o§a] §r§f";

    protected $db;
    protected $gamemanager;


   /*
   Called when the plugin enables
   */
   public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        //Database setup
        $this->db = new DataBase($this->getDatafolder() . "base.db");
        if($this->db->query("PRAGMA table_info(Games);")->num_rows == 0) {
            $this->db->query(<<<A
CREATE TABLE Games (
    name VARCHAR( 128 ) NOT NULL,
    spawnpoint VARCHAR( 128 ) NOT NULL,
    seekerspawn VARCHAR( 128 ) NOT NULL,
    max_players INT NOT NULL,
    waiting_time INT NOT NULL,
    seek_time INT NOT NULL,
    seekers_percentage INT NOT NULL
)
A
);
        }

        $this->gamemanager = new GameManager();
   }


   /*
   Called when one of the defined commands of the plugin has been called
   @param     $sender     \pocketmine\command\CommandSender
   @param     $cmd          \pocketmine\command\Command
   @param     $label         mixed
   @param     $args          array
   return bool
   */
   public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args): bool {
        switch($cmd->getName()) {
               case "hideandseek":
               if(isset($args[0])) {
                   switch(strtolower($args[0])) {
                       case "creategame":
                       case "addgame":
                       if($sender->getLevel()->getName() == $this->getLobbyWorld()->getName()) {
                           $sender->sendMessage(self::PREFIX . "§4Could not create game ! You're in the lobby level.");
                           return true;
                       } elseif(!is_null($this->getGameManager()->getGameByLevel($sender->getLevel()))) {
                           $sender->sendMessage(self::PREFIX . "§4This level is already an hide and seek game.");
                           return true;
                       } else {
                           $this->getGameManager()[] = new Game($sender->getLevel());
                           $sender->sendMessage(self::PREFIX . "§4Succefully created hide and seek game in level {$sender->getLevel()->getName()}.");
                           return true;
                       }
                       break;
                       case "deletegame":
                       case "delgame":
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           unset($this->getGameManager()[$sender->getLevel()->getName()]);
                           $sender->sendMessage(self::PREFIX . "§4Succefully deleted hide and seek game in level {$sender->getLevel()->getName()}.");
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setmaxplayers":
                       case "smp":
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           if(isset($args[1]) && is_int($args[1]) && $args[1] > 1) {
                               $game->setMaxPlayers($args[1]);
                               $sender->sendMessage(self::PREFIX . "§4Succefully set maximum amount of players of hide and seek game in level {$sender->getLevel()->getName()} to {$args[1]}.");
                           } else {
                               $sender->sendMessage("§4Usage: /hideandseek setmaxplayers <max amount>");
                           }
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setseekerspercentage":
                       case "ssp":
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           if(isset($args[1]) && is_int($args[1]) && $args[1] > 0 && $args[1] < 100) {
                               $game->setSeekersPercentage($args[1]);
                               $sender->sendMessage(self::PREFIX . "§4Succefully set seekers percentage of hide and seek game in level {$sender->getLevel()->getName()} to {$args[1]}.");
                           } else {
                               $sender->sendMessage("§4Usage: /hideandseek setseekerspercentage <percentage>");
                           }
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setwaitingtime":
                       case "setwaittime":
                       case "swt":
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           if(isset($args[1]) && is_int($args[1]) && $args[1] > 0) {
                               $game->setWaitTime($args[1]);
                               $sender->sendMessage(self::PREFIX . "§4Succefully set waiting time of hide and seek game in level {$sender->getLevel()->getName()} to {$args[1]}.");
                           } else {
                               $sender->sendMessage("§4Usage: /hideandseek setwaittime <seconds to wait>");
                           }
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setseektime":
                       case "sst":
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           if(isset($args[1]) && is_int($args[1]) && $args[1] > 0) {
                               $game->setSeekTime($args[1]);
                               $sender->sendMessage(self::PREFIX . "§4Succefully set seeking time of hide and seek game in level {$sender->getLevel()->getName()} to {$args[1]}.");
                           } else {
                               $sender->sendMessage("§4Usage: /hideandseek setseektime <minutes of seeking>");
                           }
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setspawn":
                       case "ss":
                       $pos = new \pocketmine\math\Vector3($sender->x, $sender->y, $sender->z);
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           $game->setSpawn($args[1]);
                           $sender->sendMessage(self::PREFIX . "§4Succefully set spawn of hide and seek game in level {$sender->getLevel()->getName()} to x: $pos->x, y: $pos->y, z: $pos->z.");
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setspawn":
                       case "ss":
                       $pos = new \pocketmine\math\Vector3($sender->x, $sender->y, $sender->z);
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           $game->setSpawn($args[1]);
                           $sender->sendMessage(self::PREFIX . "§4Succefully set spawn of hide and seek game in level {$sender->getLevel()->getName()} to x: $pos->x, y: $pos->y, z: $pos->z.");
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                       case "setseekersspawn":
                       case "sss":
                       $pos = new \pocketmine\math\Vector3($sender->x, $sender->y, $sender->z);
                       $game = $this->getGameManager()->getGameByLevel($sender->getLevel());
                       if(!is_null($game)) {
                           $game->setSeekersSpawn($args[1]);
                           $sender->sendMessage(self::PREFIX . "§4Succefully set seekers spawn of hide and seek game in level {$sender->getLevel()->getName()} to x: $pos->x, y: $pos->y, z: $pos->z.");
                           return true;
                       } else {
                           $sender->sendMessage(self::PREFIX . "§4You're not in an hide and seek game world.");
                       }
                       break;
                   }
                   return true;
               } else {
                   return false;
               }
               break;
        }
   }


#               _____    _____ 
#       /\     |  __ \  |_   _|
#      /  \    | |__) |   | |  
#     / /\ \   |  ___/    | |  
#    / ____ \  | |       _| |_ 
#   /_/    \_\ |_|      |_____|

    /*
    Returns the db.
    @return \Ad5001\HideAndSeek\Database
    */
    public function getDatabase() : Database {
        return $this->db;
    }

    /*
    Returns the game manager.
    @param       
    @return GameManager
    */
    public function getGameManager() : GameManager {
        return $this->gamemanager;
    }


    /*
    Returns the max players in a game.
    @return int
    */
    public function getMaxPlayers() : int {
        return (int) $this->getConfig()->get("Max players");
    }

    /*
    Returns the time to wait between 75% of max players joined and the game start (ending filling if needed)
    @return int
    */
    public function getWaitTime() : int {
        return (int) $this->getConfig()->get("Waiting time");
    }

    /*
    Returns the time that the seekers have to find all hiders (used to balance with the max players)
    @return int
    */
    public function getSeekTime() : int {
        return (int) $this->getConfig()->get("Seeking time");
    }

    /*
    Returns the world players should be tped to when game ends
    @return \pocketmine\level\Level
    */
    public function getLobbyWorld() : \pocketmine\level\Level {
        return $this->getServer()->getLevelByName($this->getConfig()->get("Lobby world")) ?? $this->getServer()->getDefaultLevel();
    }

    /*
    Returns the percentage of the players that should be hunters.
    @return int
    */
    public function getSeekersPercentage() : int {
        return (int) $this->getConfig()->get("Seekers percentage");
    }


#    ______                          _         
#   |  ____|                        | |        
#   | |__    __   __   ___   _ __   | |_   ___ 
#   |  __|   \ \ / /  / _ \ | '_ \  | __| / __|
#   | |____   \ V /  |  __/ | | | | | |_  \__ \
#   |______|   \_/    \___| |_| |_|  \__| |___/
#                                              
#                                              

    /*
    Check when a level loads to check if it's a game and then add it
    @param     $event    \pocketmine\event\level\LevelLoadEvent
    */
    public function onLevelLoad(\pocketmine\event\level\LevelLoadEvent $event) {
        $this->getGameManager()->refreshRegisterGames();
    }
}