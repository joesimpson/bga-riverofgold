<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Cards;
use Tests\Utils\PHPUnitUtil;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class SetupTest extends TestCase
{
    // ----------------------------------------------------------------------
    public function test_setupNewGame_NoExpansions(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        
        $player1Resources = json_decode(TestDatas::$players[3]['resources'], true);
        assertSame(7, $player1Resources [RESOURCE_TYPE_MONEY]);
        $player2Resources = json_decode(TestDatas::$players[4]['resources'], true);
        assertSame(8, $player2Resources [RESOURCE_TYPE_MONEY]);
    }
    public function test_setupNewGame_DraftAlternative(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_ALTERNATIVE,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
    }

    // ----------------------------------------------------------------------
    public function testEnteringState_PlayerSetup(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //Clear hand :
        unset(TestDatas::$cards[11]);
        unset(TestDatas::$cards[12]);
        unset(TestDatas::$cards[13]);

        $game->stPlayerSetup();

        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(10, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(0, TestDatas::$players[1]['skip_roll_die']);
        assertSame(2, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }

    public function testEnteringState_PlayerSetup_PatronDarling(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //Clear hand :
        unset(TestDatas::$cards[11]);
        unset(TestDatas::$cards[12]);
        unset(TestDatas::$cards[13]);

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        
        //false because of rollDie()
        assertSame(0, TestDatas::$players[1]['skip_roll_die']);
        assertSame(2, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }
    public function testEnteringState_PlayerSetup_PatronSonOfStorm(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_SON_OF_STORM;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //Clear hand :
        unset(TestDatas::$cards[11]);
        unset(TestDatas::$cards[12]);
        unset(TestDatas::$cards[13]);

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(0, TestDatas::$players[1]['skip_roll_die']);
        assertSame(3, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }
    
    public function testEnteringState_PlayerSetup_PatronLadyOfLions(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$cards[101]['type'] = PATRON_LIONS_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->stPlayerSetup();
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(json_encode([BONUS_TYPE_PLACE_LION]), TestDatas::$players[1]['bonuses']);
        //assertSame(1, TestDatas::$players[1]['skip_roll_die']);
    }
    // ----------------------------------------------------------------------
    
    
}