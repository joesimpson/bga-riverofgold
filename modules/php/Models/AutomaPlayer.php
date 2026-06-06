<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Managers\Players;

/**
 * Fake player for the automata, not saved in Player Table,
 * 
 * override custom methods 
 */
class AutomaPlayer extends Player
{  
    
    public function getUiData($currentPlayerId = null)
    {
        $data = parent::getUiData();
        $data['score'] = $this->getScore();
        $data['name'] = $this->getName();
        $data['id'] = $this->getId();
        $data['no'] = 1 + Players::getPlayersMaxNo();
        $data['clan'] = $this->getClan();
        $data['color'] = $this->getColor();
        $data['die'] = $this->getDie();
        $data['lastTurnPlayed'] = $this->isLastTurnPlayed();
        $data['is_automa'] = true;
        
        unset($data['skipRollDie']);
        unset($data['zombie']);
        unset($data['scoreAux']);
        unset($data['eliminated']);

        return $data;
    }

    public function setScore(int $value){
        Globals::setAutomaScore($value);
        Stats::set( "automa_score", null, $value );
    }
    public function getScore() : int {
        return Globals::getAutomaScore();
    }
        
    public function addPoints(int $points, bool $sendNotif = true)
    {
        if($points == 0) return;
        Globals::incAutomaScore($points);
        Stats::inc( "automa_score", $points );
        if($sendNotif) Notifications::addPoints($this,$points);
    }
        
    //Automa doesn't manage resources
    public function setResources(array $value){
    }
    public function getResources() : array {
        return [];
    }

    public function giveResource(int $nb,int $type,bool $sendNotif = true) : int
    {
        return 0;
    }

    public function giveResourceFromTile($nb, $type, $tile)
    {
        //NOTHING
    }
    
    public function giveResourceFromShoreSpace($nb, $type, $shoreSpace)
    {
        //NOTHING
    }
  
    
    public function canReceiveMoney()
    {
        return false;
    }
    public function canReceiveResource(int $resourceType) : bool
    {
        return false;
    }
    
    public function setDie(int $value){
        Globals::setAutomaDie($value);
    }
    public function getDie() : int {
        return Globals::getAutomaDie();
    }
    
    public function setBonuses(array $value){
    }
    public function getBonuses() : array {
        return [];
    }

    public function setLastTurnPlayed(bool $value){
        Globals::setAutomaLastTurnPlayed($value);
    }
    public function isLastTurnPlayed() : bool {
        return Globals::isAutomaLastTurnPlayed();
    }
    public function getLastTurnPlayed() : bool {
        return $this->isLastTurnPlayed();
    }
}
