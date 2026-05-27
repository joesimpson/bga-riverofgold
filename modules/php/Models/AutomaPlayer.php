<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;

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
        $data['clan'] = $this->getClan();
        $data['color'] = $this->getColor();
        $data['die'] = $this->getDie();
        
        $data['is_automa'] = true;
        return $data;
    }

    public function setScore(int $value){
        Globals::setAutomaScore($value);
    }
    public function getScore() : int {
        return Globals::getAutomaScore();
    }
        
    public function addPoints(int $points, bool $sendNotif = true)
    {
        if($points == 0) return;
        Globals::setAutomaScore($points);
        if($sendNotif) Notifications::addPoints($this,$points);
    }
        
    public function giveResource(int $nb,int $type,bool $sendNotif = true) : int
    {
        //Automa doesn't manage resources
        return 0;
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
}
