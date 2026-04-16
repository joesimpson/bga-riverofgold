<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;

final class EndTurnTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    
    public function test_EnteringState_OpponentBonuses(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        TestDatas::$players[2]['bonuses'] = json_encode([BONUS_TYPE_CHOICE, BONUS_TYPE_DRAW]);

        $game->stEndTurn();
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(2, TestDatas::$test_activePlayerId);
    }
    public function test_EnteringState(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        TestDatas::$players[1]['die_face'] = -1;
        Globals::setEra(1);
        Globals::setEndPlayer(null);

        $game->stEndTurn();
        
        //check die rolled :
        assertNotSame(-1, TestDatas::$players[1]['die_face']);
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    public function test_EnteringState_EmperorVisit(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[34]);
        Globals::setEra(1);

        $game->stEndTurn();
        
        assertSame(2, Globals::getEra());
        //Check owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);
        assertSame(2 + 1, TestDatas::$players[2]['player_score']);
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_EnteringState_EmperorVisitWithBonuses(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[34]);
        Globals::setEra(1);
        TestDatas::$tiles[41]['type'] = 40;

        $game->stEndTurn();
        
        //Check owner rewards :
        $expectedBonuses = json_encode([BONUS_TYPE_CHOICE]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(1, TestDatas::$test_activePlayerId);
    }
    public function test_EnteringState_LastTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        unset(TestDatas::$tiles[34]);
        unset(TestDatas::$tiles[101]);
        unset(TestDatas::$tiles[102]);
        unset(TestDatas::$tiles[103]);
        unset(TestDatas::$tiles[104]);
        unset(TestDatas::$tiles[105]);
        TestDatas::$players[1]['die_face'] = -1;

        $game->stEndTurn();
        
        //Score +NB_POINTS_FOR_GAME_END
        assertSame(24, TestDatas::$players[TestDatas::$test_activePlayerId]['player_score']);
        //check die NOT rolled :
        assertSame(-1, TestDatas::$players[1]['die_face']);
        assertSame(true, TestDatas::$players[1]['last_turn_played']);
        assertSame(1, Globals::getEndPlayer());
        assertSame(true, Globals::isLastTurnTriggered());
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }

    
    // -------------------------------------------------
}