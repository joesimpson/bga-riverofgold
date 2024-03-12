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
 * states.inc.php
 *
 * RiverOfGold game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!
 
require_once 'modules/php/constants.inc.php';

/*
    "Visual" States Diagram :

        SETUP
        |
        v
        clanSelection
        |
        v
        playerSetup
                |
                v
 /<----------- nextTurn     <-------------------------\                                
 |              |                                     |
 |              v                                     |
 |             beforeTurn                             |
 |                |                                   |
 |                |                                   |
 |                v                                   |
 |                playerTurn -------------------------/
 v        
 \-> endGameScoring
        | 
        v
        preEndOfGame
        | 
        v
        END
*/

$machinestates = array(

    // The initial state. Please do not modify.
    ST_GAME_SETUP => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => ST_CLAN_SELECTION)
    ),
    
    ST_CLAN_SELECTION => array(
        "name" => "clanSelection",
        "description" => clienttranslate('Assigning clans to players'),
        "type" => "game",
        "action" => "stClanSelection",
        "transitions" => [ 
            "next" => ST_PLAYER_SETUP,
        ],
    ),
    
    ST_PLAYER_SETUP => array(
        "name" => "playerSetup",
        "description" => clienttranslate('Setting up players ressources'),
        "type" => "game",
        "action" => "stPlayerSetup",
        "transitions" => [ 
            "next" => ST_NEXT_TURN,
        ],
    ),
    
    ST_NEXT_TURN => array(
        "name" => "nextTurn",
        "description" => clienttranslate('Next turn'),
        "type" => "game",
        "action" => "stNextTurn",
        "updateGameProgression" => true,
        "transitions" => [ 
            "next" => ST_BEFORE_TURN,
            "end" => ST_END_SCORING,
        ],
    ),

    //Checks before next turn : almost unused, may be used by some clans (Darling)
    ST_BEFORE_TURN => array(
        "name" => "beforeTurn",
        "description" => clienttranslate('${actplayer} may spend favor to choose the die face'),
        "descriptionmyturn" => clienttranslate('${you} may spend favor to choose the die face'),
        "type" => "activeplayer",
        "action" => "stBeforeTurn",
        "updateGameProgression" => true,
        "transitions" => [ 
            "next" => ST_PLAYER_TURN,
            "zombiePass" => ST_PLAYER_TURN,
        ],
    ),

    ST_PLAYER_TURN => array(
        "name" => "playerTurn",
        "args" => "argPlayerTurn",
        "description" => clienttranslate('${actplayer} must take an action'),
        "descriptionmyturn" => clienttranslate('${you} must take an action'),
        "type" => "activeplayer",
        "possibleactions" => [
            "actBuild", 
            "actSail", 
            "actDeliver", 
            "actTrade", 
            "actSpendFavor", 
            'actRestart',
        ],
        "transitions" => [ 
            "build" => ST_PLAYER_TURN_BUILD, 
            "trade" => ST_PLAYER_TURN_TRADE, 
            "favor" => ST_PLAYER_TURN_DIVINE_FAVOR, 
            "next" => ST_NEXT_TURN, 
            "zombiePass" => ST_NEXT_TURN,
        ],
    ),
    
    ST_PLAYER_TURN_BUILD => array(
        "name" => "build",
        "args" => "argBuild",
        "description" => clienttranslate('${actplayer} must select a building and a shore space'),
        "descriptionmyturn" => clienttranslate('${you} must select a building and a shore space'),
        "type" => "activeplayer",
        "possibleactions" => [
            "actBuildSelect", 
            'actRestart',
        ],
        "transitions" => [ 
            "bonus" => ST_BONUS_CHOICE, 
            "next" => ST_CONFIRM_CHOICES, 
            "zombiePass" => ST_CONFIRM_CHOICES,
        ],
    ),
    
    ST_BONUS_CHOICE => [
        'name' => 'bonusChoice',
        'description' => clienttranslate('${actplayer} must select a bonus'),
        'descriptionmyturn' => clienttranslate('${you} must select a bonus'),
        'type' => 'activeplayer',
        'args' => 'argBonusChoice',
        'possibleactions' => [
            'actBonus', 
            'actRestart'
        ],
        'transitions' => [
            'next' => ST_CONFIRM_CHOICES,
            'zombiePass'=> ST_CONFIRM_CHOICES,
        ],
    ],

    ST_PLAYER_TURN_TRADE => array(
        "name" => "trade",
        "args" => "argTrade",
        "description" => clienttranslate('${actplayer} must select the trade'),
        "descriptionmyturn" => clienttranslate('${you} must select the trade'),
        "type" => "activeplayer",
        "possibleactions" => [
            "actTradeSelect", 
            'actRestart',
        ],
        "transitions" => [ 
            "next" => ST_PLAYER_TURN, 
            "zombiePass" => ST_PLAYER_TURN,
        ],
    ),
    
    ST_PLAYER_TURN_DIVINE_FAVOR => array(
        "name" => "spendFavor",
        "args" => "argSpendFavor",
        "description" => clienttranslate('${actplayer} must select the die face'),
        "descriptionmyturn" => clienttranslate('${you} must select the die face'),
        "type" => "activeplayer",
        "possibleactions" => [
            "actDFSelect", 
            'actRestart',
        ],
        "transitions" => [ 
            "next" => ST_PLAYER_TURN, 
            "zombiePass" => ST_PLAYER_TURN,
        ],
    ),

    ST_CONFIRM_CHOICES => [
        'name' => 'confirmChoices',
        'description' => '',
        'type' => 'game',
        'action' => 'stConfirmChoices',
        'transitions' => [
          '' => ST_CONFIRM_TURN,
        ],
    ],
    
    ST_CONFIRM_TURN => [
        'name' => 'confirmTurn',
        'description' => clienttranslate('${actplayer} must confirm or restart their turn'),
        'descriptionmyturn' => clienttranslate('${you} must confirm or restart your turn'),
        'type' => 'activeplayer',
        'args' => 'argsConfirmTurn',
        'action' => 'stConfirmTurn',
        'possibleactions' => ['actConfirmTurn', 'actRestart'],
        'transitions' => [
          'confirm' => ST_NEXT_TURN,
          'zombiePass'=> ST_NEXT_TURN,
        ],
    ],

    ST_END_SCORING => array(
        "name" => "scoring",
        "description" => clienttranslate('Scoring'),
        "type" => "game",
        "action" => "stScoring",
        "transitions" => [ 
            "next" => ST_PRE_END_OF_GAME,
        ],
    ),
    
    ST_PRE_END_OF_GAME => array(
        "name" => "preEndOfGame",
        "description" => '',
        "type" => "game",
        "action" => "stPreEndOfGame",
        "transitions" => [ 
            //"next" => ST_END_GAME,
            "next" => 96,
        ],
    ),
   
    //END GAME TESTING STATE
    96 => [
        "name" => "playerGameEnd",
        "description" => ('${actplayer} Game Over'),
        "descriptionmyturn" => ('${you} Game Over'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => ["endGame"],
        "transitions" => [
            "next" => ST_END_GAME,
            "loopback" => 96 
        ] 
    ],
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



