<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class NextTurnTest extends TestCase
{

    public function testEnteringState_turn1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(0);
        TestDatas::$players[1]['last_turn_played'] = false;
        TestDatas::$players[2]['last_turn_played'] = false;

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(1, Globals::getTurnPlayer());
        assertSame(false, Globals::isAutomaActive());
        assertSame(ST_BEFORE_TURN, GamestateMachine::$test_current_state);
    }
     
    public function testEnteringState_turn2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(1);
        TestDatas::$players[1]['last_turn_played'] = false;
        TestDatas::$players[2]['last_turn_played'] = false;

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(2, Globals::getTurnPlayer());
        assertSame(false, Globals::isAutomaActive());
        assertSame(ST_BEFORE_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_turnLast(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(123456789);//just for visibility, doesn't affect last turn
        TestDatas::$players[1]['last_turn_played'] = true;
        TestDatas::$players[2]['last_turn_played'] = true;

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(1, Globals::getTurnPlayer());
        assertSame(false, Globals::isAutomaActive());
        assertSame(ST_END_SCORING, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_turnLastWithAutoma(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(123456789);//just for visibility, doesn't affect last turn
        TestDatas::$players[1]['last_turn_played'] = true;
        TestDatas::$players[2]['last_turn_played'] = true;
        Globals::setAutomaLastTurnPlayed(true);

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(1, Globals::getTurnPlayer());
        assertSame(false, Globals::isAutomaActive());
        assertSame(ST_END_SCORING, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_turnLast_beforeAutomaTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        TestDatas::$players[1]['last_turn_played'] = true;
        TestDatas::$players[2]['last_turn_played'] = true;
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);

        $game->stNextTurn();
        
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[201]['card_location']);
        assertSame(1, TestDatas::$cards[201]['card_state']);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }

    public function testEnteringState_beforeAutomaTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);

        $game->stNextTurn();
        
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[201]['card_location']);
        assertSame(1, TestDatas::$cards[201]['card_state']);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_beforeAutomaTurn_emptyActionDeck(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_AUTOMA_ACTION) $card['card_location'] = CARD_AUTOMA_LOCATION_PLAYED;

        $game->stNextTurn();
        
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[201]['card_location']);
        assertSame(1, TestDatas::$cards[201]['card_state']);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
}