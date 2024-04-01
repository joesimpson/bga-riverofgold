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
 
    public function actSkip()
    {
      self::setAjaxMode();
      $this->game->actSkip();
      self::ajaxResponse();
    }
    public function actDarlingFavor()
    {
      self::setAjaxMode();
      $dieFace = self::getArg( "d", AT_posint, true );
      $this->game->actDarlingFavor($dieFace);
      self::ajaxResponse();
    }
    public function actSpendFavor()
    {
      self::setAjaxMode();
      $this->game->actSpendFavor();
      self::ajaxResponse();
    }
    public function actDFSelect()
    {
      self::setAjaxMode();
      $dieFace = self::getArg( "d", AT_posint, true );
      $this->game->actDFSelect($dieFace);
      self::ajaxResponse();
    }
    public function actTrade()
    {
      self::setAjaxMode();
      $this->game->actTrade();
      self::ajaxResponse();
    }
    public function actTradeSelect()
    {
      self::setAjaxMode();
      $typeSrc = self::getArg( "src", AT_posint, true );
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actTradeSelect($typeSrc,$typeDest);
      self::ajaxResponse();
    }
    public function actBuild()
    {
      self::setAjaxMode();
      $this->game->actBuild();
      self::ajaxResponse();
    }
    public function actBonus()
    {
      self::setAjaxMode();
      $resourceType = self::getArg( "r", AT_posint, true );
      $this->game->actBonus($resourceType);
      self::ajaxResponse();
    }
    public function actSail()
    {
      self::setAjaxMode();
      $this->game->actSail();
      self::ajaxResponse();
    }
    public function actSailSelect()
    {
      self::setAjaxMode();
      $shipId = self::getArg( "s", AT_posint, true );
      $riverSpace = self::getArg( "r", AT_posint, true );
      $this->game->actSailSelect($shipId,$riverSpace);
      self::ajaxResponse();
    }
    public function actDeliver()
    {
      self::setAjaxMode();
      $this->game->actDeliver();
      self::ajaxResponse();
    }
    public function actDeliverSelect()
    {
      self::setAjaxMode();
      $cardId = self::getArg( "c", AT_posint, true );
      $this->game->actDeliverSelect($cardId);
      self::ajaxResponse();
    }
    
    public function actBuildSelect()
    {
      self::setAjaxMode();
      $position = self::getArg( "p", AT_posint, true );
      $tileId = self::getArg( "t", AT_posint, true );
      $this->game->actBuildSelect($position,$tileId);
      self::ajaxResponse();
    }

    public function actTakeCard()
    {
      self::setAjaxMode();
      $cardId = self::getArg( "c", AT_posint, true );
      $this->game->actTakeCard($cardId);
      self::ajaxResponse();
    }
    public function actDiscardCard()
    {
      self::setAjaxMode();
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
      $this->game->actConfirmTurn();
      self::ajaxResponse();
    }

    public function actRestart()
    {
      self::setAjaxMode();
      $this->game->actRestart();
      self::ajaxResponse();
    }

    public function actUndoToStep()
    {
      self::setAjaxMode();
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
  

