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
use SQLite3;

use Ad5001\HideAndSeek\Main;

class DataBase extends SQLite3 {
    
    /*
    Gets a data from a line where $data["key"] = $data["value"]
    @param     $dataToGet    string
    @param     $data    array
    @return array|bool
    */
    public function get(string $dataToGet, array $data) {
        $table = $data["table"];
        unset($data["table"]);
        $str = $this->buildQueryArgs($data);
        $result = $this->query("SELECT $dataToGet FROM $table$str");
        return $result;
    }


    /*
    Sets data to a database where $data["key"] = $data["value"];
    @param     $key    string
    @param     $value     string
    @param     $data    array
    */
    public function set(string $key, string $value, array $data) {
        $table = $data["table"];
        unset($data["table"]);
        $str = $this->buildQueryArgs($data);
        return $this->query("UPDATE $table SET $key = '$value'$str");
    }


    /*
    Inserts a row into the database
    @param     $table    string
    @param     $data    array
    */
    public function insert(string $table, array $data) {
        return $this->query("INSERT INTO $table VALUES ('" . implode("', '", $data) . "')");
    }


    /*
    Deletes a row from the database
    @param     $table    string
    @param     $data    array
    */
    public function delete(string $table, array $data) {
        $str = $this->buildQueryArgs($data);
        return $this->query("DELETE FROM $table$str");
    }

    /*
    Formats an SQL query from an array
    @param     $data    array
    @return string
    */
    public function buildQueryArgs(array $data) : string {
        $str = " WHERE ";
        foreach($data as $k => $d) $str .= "$k = '$d' AND ";
        return strlen($str) !== 7 ? substr($str, 0, strlen($str) - 5) : "";
    }

    /*
    Checks queries and return it. To set custom params/
    @param     $qry    string
    @return SQLite3Result|bool
    */
    public function query($qry) {
        $this->getLogger()->debug($qry);
        $res = parent::query($qry);
        if($res instanceof \SQLite3Result) self::setNumRows($res);
        return $res;
    }

    /*
    Sets a number of rows in of a query
    @param     &$result    \SQLite3Result
    */
    protected static function setNumRows(\SQLite3Result &$result) {
        $n = 0;
        while($result->fetchArray()) $n++;
        $result->reset();
        $result->num_rows = $n;
    }
}