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

require_once 'modules/php/Models/Enums.php';

use Bga\GameFramework\Actions\CheckAction;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Preferences;
use ROG\Exceptions\UserException;
use ROG\Helpers\AutomaEngine;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

class RiverOfGoldNightMarket extends \Bga\GameFramework\Table
{
    use ROG\DebugTrait;
    use ROG\States\BeforeTurnTrait;
    use ROG\States\BonusChoiceTrait;
    use ROG\States\BonusResourceTrait;
    use ROG\States\BonusSecondMarkerTrait;
    use ROG\States\BonusSellGoodsTrait;
    use ROG\States\BonusSetDieTrait;
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
    public AutomaEngine $automaEngine;
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
        $this->automaEngine = new AutomaEngine($this);
	}
    public static function get()
    {
      return self::$instance;
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
        $current_player_id = $this->getCurrentPId();    // !! We must only return informations visible by this player !!
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
            'customers' => Cards::countInLocation(CARD_LOCATION_DECK),
            'customerDiscard' => Cards::countInLocation(CARD_LOCATION_DISCARD),
            'automaDeck' => Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK),
            'automaPlayed' => Cards::countInLocation(CARD_AUTOMA_LOCATION_PLAYED),
            'hiddenDeliv' => [
            ],
          ],
          'firstPlayer' => $firstPlayer,
          'endTriggered' => Globals::isLastTurnTriggered(),
          'endScoring' => Globals::getEndScoring(),
          'customerTypes' => Globals::getCustomerTypes(),
          'customTracks' => Globals::getRegionCustomTracks(),
          
          'automa_level' => Globals::getOptionSeishin(),
          'version'=> Utils::gameVersion(),
          'constants' => [
            'INFLUENCE_TRACK_REWARDS' => INFLUENCE_TRACK_REWARDS,
            'CUSTOM_REGION_TRACKS' => CUSTOM_REGION_TRACKS,
          ],
          'enums' => Utils::getEnumsUI(),
        ];
        if(Utils::isGameWithAutoma()){
            $result['automa_player'] = Players::automaPlayer()->getUiData();
            $result['deckSize']['hiddenDeliv'][AUTOMA_PLAYER_ID] = Cards::countPlayerHiddenDeliveredCustomers(AUTOMA_PLAYER_ID);
        }
        return $result;
    }
    
    /**
     * Returns an array of user preference colors to game colors.
     * Game colors must be among those which are passed to `Table::reattributeColorsBasedOnPreferences()`.
     *
     * Each game color can be an array of suitable colors, or a single color:
     *
     * ```
     * [
     *    // The first available color chosen:
     *    'ff0000' => ['990000', 'aa1122'],
     *    // This color is chosen, if available
     *    '0000ff' => '000099',
     * ]
     * ```
     *
     * If no color can be matched from this array, then the default implementation is used.
     *
     * @return array<string, ?string>
     */
    function getSpecificColorPairings(): array {
        return array(
            "ff0000" /* Red */         => 'ff0000',
            "008000" /* Green */       => '298a47', //new green in v2 with Dragon clan
            "0000ff" /* Blue */        => '0000ff',
            "ffff00" /* Yellow */      => 'ffff00',
            "e94190" /* Pink */        => '982fff',
            "982fff" /* Purple */      => '982fff',
            //"72c3b1" /* Cyan */        => '008000', // map to 'green' used in v1 for mantis clan which is closer to cyan
            "72c3b1" /* Cyan */        => '72c3b1',
            "f07f16" /* Orange */      => 'f07f16',
            "bdd002" /* Khaki green */ => '298a47',
            "7b7b7b" /* Gray */        => 'ffffff',
            //"000000" /* Black */       => 'ffffff',
            "ffffff" /* White */       => 'ffffff',
        );
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
        $initialDeckSizes = [ 1=>21, 2 =>21, 3=>25, 4=>29, 5=>33, ];
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

    #[PossibleAction]
    #[CheckAction(false)]
    /**
     * @deprecated NOT necessary IN THIS GAME ! (no server side pref)
     */
    function actChangePref(?int $pref, ?int $value)
    {
      Preferences::set($this->getCurrentPId(), $pref, $value);
    }

    /**
    * Check Server version to compare with client version : throw an error in case it 's not the same
    * From https://en.doc.boardgamearena.com/BGA_Studio_Cookbook#Force_players_to_refresh_after_new_deploy
    */
    public function checkVersion(int $clientVersion)
    {
        if ($clientVersion != Utils::gameVersion()) {
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
                case 'bonusChoice':
                    $player = Players::get($active_player);
                    //Erase bonuses to avoid infinite loop at the end of the turn
                    $player->setBonuses([]);
                    $this->gamestate->nextState( "zombiePass" );
                    break;
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
        
        if( $from_version <= 2404111852 )
        {
            $sql = "ALTER TABLE DBPREFIX_player ADD `skip_roll_die` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'This player will NOT roll the die for next turn : 0/1';";
            $this->applyDbUpgradeToAllDB($sql);
        }
        //Fixing Clan after color reattribution in previous patch :
        if( $from_version <= 2408231642 && $from_version >2406262358 )
        {
            foreach (CLANS_COLORS as $color => $clan_id) {
                $sql = "UPDATE DBPREFIX_player set player_clan=$clan_id where player_color='$color';";
                $this->applyDbUpgradeToAllDB($sql);
            }
        }

        if( $from_version <= 2602201646 )
        {
            $sql = "ALTER TABLE DBPREFIX_tiles ADD `player_id` int(10) NULL;";
            $this->applyDbUpgradeToAllDB($sql);
            //FIX Mantis color with new 4 colors
            $sql = "UPDATE DBPREFIX_player set player_color = '72c3b1' where player_color = '008000';";
            $this->applyDbUpgradeToAllDB($sql);

            $baseTypes = json_encode([CUSTOMER_TYPE_ARTISAN,CUSTOMER_TYPE_ELDER,CUSTOMER_TYPE_MERCHANT,CUSTOMER_TYPE_MONK,CUSTOMER_TYPE_NOBLE]);
            $sql = "INSERT IGNORE INTO DBPREFIX_global_variables (`name`, `value`) VALUES ('customerTypes', '$baseTypes' );";
            $this->applyDbUpgradeToAllDB($sql);
            $sql = "UPDATE DBPREFIX_global_variables set `value` = '$baseTypes' where JSON_LENGTH(`value`) = 0 AND `name` = 'customerTypes';";
            $this->applyDbUpgradeToAllDB($sql);
        }
    }    
     
    /////////////////////////////////////////////////////////////
    // Exposing protected methods, please use at your own risk //
    /////////////////////////////////////////////////////////////

    // Exposing protected method getCurrentPlayerId
    public function getCurrentPId($bReturnNullIfNotLogged = false)
    {
        return $this->getCurrentPlayerId($bReturnNullIfNotLogged);
    }
}
