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
 
    public function actBuild()
    {
      self::setAjaxMode();
      $this->game->actBuild();
      self::ajaxResponse();
    }
    public function actSail()
    {
      self::setAjaxMode();
      $this->game->actSail();
      self::ajaxResponse();
    }
    public function actDeliver()
    {
      self::setAjaxMode();
      $this->game->actDeliver();
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

  }
  

