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

use pocketmine\Server;
use pocketmine\Player;

use Ad5001\HideAndSeek\Main;

class GameManager {

    protected $games = [];
    protected $gamesNames = [];
    
    /*
    Gets a game by it's name.
    @param     $name    string
    @return Game|null
    */
    public function getGameByName(string $name) {
        if(isset($this->gamesNames[$name])) return $this->games[$this->gamesNames[$name]];
        return null;
    }
    
    /*
    Gets a game by it's name.
    @param     $id    int
    @return Game|null
    */
    public function getGameById(int $id) {
        if(isset($this->games[$id])) return $this->games[$id];
        return null;
    }
    
    /*
    Gets a game by it's level.
    @param     $level    \pocketmine\level\Level
    @return Game|null
    */
    public function getGameByLevel(\pocketmine\level\Level $level) {
        return $this->getGameByName($level->getName());
    }


    /*
    Returns the id of a game. Better use $game->getId()
    @param     $game    Game
    @return int
    */
    public function getGameId(Game $game) : int {
        if(isset($this->gamesNames[$game->getName()])) return $this->gamesNames[$game->getName()];
        return -1;
    }



    /*
    Used when try to access unknown/as array data. Can use id or name.
    @param     $name    string
    @return Game|null
    */
    public function __get(string $name) {
        $pass = $this->getGameByName($name);
        if(is_null($pass)) $pass = $this->getGameByName($name);
        return $pass;
    }


    /*
    Registers a game when using a set as class/array
    @param     $name    string
    @param     $value    Game
    */
    public function __set(string $name, $value) {
        if($value instanceof Game) {
            if(!isset($this->gamesNames[$name])) {
                $result = $this->getMain()->getDatabase()->get("*", ["table" => "Games"]);
                $this->games[$result->num_rows] = $value;
                $this->gamesNames[$value->getName()] = $result->num_rows;
                $this->getMain()->getLogger()->notice("Succefully registered game level {$value->getName()}.");
            }
        } else {
            throw new Exception("Value should be a game.");
        }
    }


    /*
    Unregisters a game when unseting it. Can use id or name.
    @param     $name    string
    */
    public function __unset(string $name) {
        if(!is_null($this->getGameByName($name))) {
            $this->getMain()->getDataBase()->delete("Games", ["name" => $name]);
            unset($this->gamesNames[$name]);
            unset($this->games[$this->gamesNames[$name]]);
        } else {
            $this->getMain()->getDataBase()->delete("Games", ["name" => $this->games[$id]->getName()]);
            unset($this->games[$name]);
        }
    }

    /*
    Checks if a game exists
    @param     $namevariable    string
    @return bool
    */
    public function __isset(string $name) : bool {
        if(!is_null($this->getGameByName($name)) || !is_null($this->getGameById($name))) {
            return true;
        }
        return false;
    }


    /*
    Returns main's instance.
    @return Main
    */
    public function getMain() : Main {
        return Server::getInstance()->getPluginManager()->getPlugin("HideAndSeek");
    }


    /*
    Registers all games which haven't been registered yet' game.
    @param     $db    DataBase
    */
    public function refreshRegisterGames(DataBase $db) {
        $result = $db->get("*", ["table" => "Games"]);
        if($result !== false) {
            while($row = $result->fetchArray()) {
                if(is_null($row["name"])) return;
                if(is_null($this->gamesName[$row["name"]]) && ($lvl = $this->getMain()->getServer()->getLevelByName($row["name"])) !== null) { // Game doesn't exists && level is loaded
                    $this->games[$row["Id"]] = new Game($lvl);
                    $this->gamesNames[$row["name"]] = $row["Id"];
                    $this->getMain()->getLogger()->notice("Succefully registered game level {$row['name']}.");
                }
            }
        }
    }

}