<?php

namespace ROG\Models;

/**
 * Fake player object for the "flying clan markers", not saved in Player Table,
 * 
 * override custom methods 
 */
class VirtualPlayer extends Player
{  
    public function getUiData($currentPlayerId = null)
    {
        $data = [];
        $data['id'] = $this->getId();
        $data['clan'] = $this->getClan();
        $data['color'] = $this->getColor();

        return $data;
    }
}
