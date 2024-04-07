<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * RiverOfGold implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * riverofgold.action.php
 *
 * RiverOfGold main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/riverofgold/riverofgold/myAction.html", ...)
 *
 */
  
  
  class action_riverofgold extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "riverofgold_riverofgold";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there

    /* Check Helper, not a real action */
    private function checkVersion()
    {
      $clientVersion = (int) self::getArg('version', AT_int, false);
      $this->game->checkVersion($clientVersion);
    }
 
    public function actSkip()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actSkip();
      self::ajaxResponse();
    }
    
    public function actSkipBonuses()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actSkipBonuses();
      self::ajaxResponse();
    }
    public function actStop()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actStop();
      self::ajaxResponse();
    }
    public function actDarlingFavor()
    {
      self::setAjaxMode();
      self::checkVersion();
      $dieFace = self::getArg( "d", AT_posint, true );
      $this->game->actDarlingFavor($dieFace);
      self::ajaxResponse();
    }
    public function actSpendFavor()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actSpendFavor();
      self::ajaxResponse();
    }
    public function actDFSelect()
    {
      self::setAjaxMode();
      self::checkVersion();
      $dieFace = self::getArg( "d", AT_posint, true );
      $this->game->actDFSelect($dieFace);
      self::ajaxResponse();
    }
    public function actTrade()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actTrade();
      self::ajaxResponse();
    }
    public function actTradeSelect()
    {
      self::setAjaxMode();
      self::checkVersion();
      $typeSrc = self::getArg( "src", AT_posint, true );
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actTradeSelect($typeSrc,$typeDest);
      self::ajaxResponse();
    }
    
    public function actSell()
    {
      self::setAjaxMode();
      self::checkVersion();
      $typeSrc = self::getArg( "src", AT_posint, true );
      $this->game->actSell($typeSrc);
      self::ajaxResponse();
    }
    public function actBuild()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actBuild();
      self::ajaxResponse();
    }
    public function actBonus()
    {
      self::setAjaxMode();
      self::checkVersion();
      $bonusType = self::getArg( "t", AT_posint, true );
      $this->game->actBonus($bonusType);
      self::ajaxResponse();
    }
    
    public function actBonusResource()
    {
      self::setAjaxMode();
      self::checkVersion();
      $resourceType = self::getArg( "r", AT_posint, true );
      $this->game->actBonusResource($resourceType);
      self::ajaxResponse();
    }
    public function actBonus3Money()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actBonus3Money();
      self::ajaxResponse();
    }
    public function actBonusUpgrade()
    {
      self::setAjaxMode();
      self::checkVersion();
      $shipId = self::getArg( "s", AT_posint, true );
      $this->game->actBonusUpgrade($shipId);
      self::ajaxResponse();
    }
    
    public function actBonusSecondMarker()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tileId = self::getArg( "t", AT_posint, true );
      $this->game->actBonusSecondMarker($tileId);
      self::ajaxResponse();
    }
    public function actSail()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actSail();
      self::ajaxResponse();
    }
    public function actSailSelect()
    {
      self::setAjaxMode();
      self::checkVersion();
      $shipId = self::getArg( "s", AT_posint, true );
      $riverSpace = self::getArg( "r", AT_posint, true );
      $this->game->actSailSelect($shipId,$riverSpace);
      self::ajaxResponse();
    }
    public function actDeliver()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actDeliver();
      self::ajaxResponse();
    }
    public function actDeliverSelect()
    {
      self::setAjaxMode();
      self::checkVersion();
      $cardId = self::getArg( "c", AT_posint, true );
      $this->game->actDeliverSelect($cardId);
      self::ajaxResponse();
    }
    
    public function actBuildSelect()
    {
      self::setAjaxMode();
      self::checkVersion();
      $position = self::getArg( "p", AT_posint, true );
      $tileId = self::getArg( "t", AT_posint, true );
      $this->game->actBuildSelect($position,$tileId);
      self::ajaxResponse();
    }

    public function actTakeCard()
    {
      self::setAjaxMode();
      self::checkVersion();
      $cardId = self::getArg( "c", AT_posint, true );
      $this->game->actTakeCard($cardId);
      self::ajaxResponse();
    }
    public function actDiscardCard()
    {
      self::setAjaxMode();
      self::checkVersion();
      $cardId = self::getArg( "c", AT_posint, true );
      $this->game->actDiscardCard($cardId);
      self::ajaxResponse();
    }

      

    ///////////////////
    /////  UNDO   /////
    ///////////////////

    public function actConfirmTurn()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actConfirmTurn();
      self::ajaxResponse();
    }

    public function actRestart()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actRestart();
      self::ajaxResponse();
    }

    public function actUndoToStep()
    {
      self::setAjaxMode();
      self::checkVersion();
      $stepId = self::getArg('stepId', AT_posint, false);
      $this->game->actUndoToStep($stepId);
      self::ajaxResponse();
    }

    ///////////////////
    /////  PREFS  /////
    ///////////////////

    public function actChangePref()
    {
      self::setAjaxMode();
      $pref = self::getArg('pref', AT_posint, false);
      $value = self::getArg('value', AT_posint, false);
      $this->game->actChangePreference($pref, $value);
      self::ajaxResponse();
    }
  }
  

