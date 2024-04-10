<?php
 /**
  *------
  * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
  * RiverOfGold implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * riverofgold.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'ROG') {
      array_shift($classParts);
      $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
      if (file_exists($file)) {
        require_once $file;
      } else {
        var_dump('Cannot find file : ' . $file);
      }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

use ROG\Core\Globals;
use ROG\Core\Preferences;
use ROG\Exceptions\UserException;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

class RiverOfGold extends Table
{
    use ROG\DebugTrait;
    use ROG\States\BeforeTurnTrait;
    use ROG\States\BonusChoiceTrait;
    use ROG\States\BonusResourceTrait;
    use ROG\States\BonusSecondMarkerTrait;
    use ROG\States\BonusSellGoodsTrait;
    use ROG\States\BonusUpgradeShipTrait;
    use ROG\States\BuildTrait;
    use ROG\States\ClanSelectionTrait;
    use ROG\States\ConfirmUndoTrait;
    use ROG\States\DeliverTrait;
    use ROG\States\DiscardTrait;
    use ROG\States\DivineFavorTrait;
    use ROG\States\DraftTrait;
    use ROG\States\EndTurnTrait;
    use ROG\States\NextTurnTrait;
    use ROG\States\PlayerTurnTrait;
    use ROG\States\SailTrait;
    use ROG\States\ScoringTrait;
    use ROG\States\SetupTrait;
    use ROG\States\TradeTrait;

    public static $instance = null;
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        self::$instance = $this;
        self::initGameStateLabels( array( 
            'logging' => 10,
        ) );        
	}
    public static function get()
    {
      return self::$instance;
    }
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "riverofgold";
    }	

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    public function getAllDatas()
    {
        $current_player_id = self::getCurrentPId();    // !! We must only return informations visible by this player !!
        // Gather all information about current game situation (visible by player $current_player_id).
        $firstPlayer = Globals::getFirstPlayer();

        $result = [
          'prefs' => Preferences::getUiData($current_player_id),
          'players' => Players::getUiData($current_player_id),
          'cards' => Cards::getUiData($current_player_id),
          'tiles' => Tiles::getUiData($current_player_id),
          //'shore' => ShoreSpaces::getUiData(),
          'meeples' => Meeples::getUiData($current_player_id),
          'turn' => Globals::getTurn(),
          'era' => Globals::getEra(),
          'deckSize' => [
            'era1' => Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_1),
            'era2' => Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_2),
          ],
          'firstPlayer' => $firstPlayer,
          'endTriggered' => Globals::isLastTurnTriggered(),
          'version'=> intval($this->gamestate->table_globals[BGA_GAMESTATE_GAMEVERSION]),
        ];
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $nbPlayers = Players::count();
        $remainingPlayers = Players::countRemainingPlayers();
        $initialDeckSizes = [ 2 =>21, 3=>25, 4=>29 ];
        $initialDeckSize = $initialDeckSizes[$nbPlayers];
        $deckSize = Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_1) 
            + Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_2);
        $buildRowSize = Tiles::countInLocation(TILE_LOCATION_BUILDING_ROW);
        $usefulBuildRow = min($remainingPlayers, $buildRowSize);
        $progress = ($initialDeckSize - $deckSize - $usefulBuildRow) / $initialDeckSize;

        return $progress * 100;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function actChangePreference($pref, $value)
    {
      Preferences::set($this->getCurrentPId(), $pref, $value);
    }

    /**
    * Check Server version to compare with client version : throw an error in case it 's not the same
    * From https://en.doc.boardgamearena.com/BGA_Studio_Cookbook#Force_players_to_refresh_after_new_deploy
    */
    public function checkVersion(int $clientVersion)
    {
        if ($clientVersion != intval($this->gamestate->table_globals[BGA_GAMESTATE_GAMEVERSION])) {
            throw new UserException('!!!checkVersion');
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in riverofgold.action.php)
    */

//-> See States package
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

//-> See States package

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
//-> See States package
//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
     
    /////////////////////////////////////////////////////////////
    // Exposing protected methods, please use at your own risk //
    /////////////////////////////////////////////////////////////

    // Exposing protected method getCurrentPlayerId
    public static function getCurrentPId($bReturnNullIfNotLogged = false)
    {
        return self::getCurrentPlayerId($bReturnNullIfNotLogged);
    }
    // Exposing protected method translation
    public static function translate($text)
    {
        return self::_($text);
    }
}
