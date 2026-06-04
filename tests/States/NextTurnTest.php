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
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "checkORewards",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
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
        $expectedNotifs = [
            "reshuffleAutomaActionDeck--123",
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "checkORewards",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[201]['card_location']);
        assertSame(1, TestDatas::$cards[201]['card_state']);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_beforeAutomaTurn_claimMasteries_Left(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        Globals::setAutomaDie(3);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_AUTOMA_ACTION) $card['card_location'] = CARD_AUTOMA_LOCATION_PLAYED;
        TestDatas::$cards[201]['card_location'] = CARD_AUTOMA_LOCATION_DECK;
        $expectedMasteryId = 1;
        $expectedPositionOnMastery = 1;
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "checkORewards",
            "reshuffleAutomaActionDeck--123",
            "newClanMarker--123",
            "claimMC--123",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        //card is reshuffled
        assertSame(CARD_AUTOMA_LOCATION_DECK, TestDatas::$cards[201]['card_location']);
        assertSame(5, Globals::getAutomaScore());//+5
        assertSame(5, TestDatas::$stats['table'][14]);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => $expectedPositionOnMastery, 'meeple_location'=> "tile-$expectedMasteryId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ],TestDatas::$tokens[43],  );
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_beforeAutomaTurn_claimMasteries_Right(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        Globals::setAutomaDie(4);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_AUTOMA_ACTION) $card['card_location'] = CARD_AUTOMA_LOCATION_PLAYED;
        TestDatas::$cards[201]['card_location'] = CARD_AUTOMA_LOCATION_DECK;
        TestDatas::$tokens[25]['meeple_state']--; 
        $expectedMasteryId = 3;
        $expectedPositionOnMastery = 1;
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "checkORewards",
            "reshuffleAutomaActionDeck--123",
            "newClanMarker--123",
            "claimMC--123",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        //card is reshuffled
        assertSame(CARD_AUTOMA_LOCATION_DECK, TestDatas::$cards[201]['card_location']);
        assertSame(5, Globals::getAutomaScore());//+5
        assertSame(5, TestDatas::$stats['table'][14]);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => $expectedPositionOnMastery, 'meeple_location'=> "tile-$expectedMasteryId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ], TestDatas::$tokens[43],  );
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_beforeAutomaTurn_claimMasteries_Middle(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        Globals::setAutomaDie(4);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_AUTOMA_ACTION) $card['card_location'] = CARD_AUTOMA_LOCATION_PLAYED;
        TestDatas::$cards[201]['card_location'] = CARD_AUTOMA_LOCATION_DECK;
        TestDatas::$tokens[25]['meeple_state']--; 
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-3",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ];
        $expectedMasteryId = 2;
        $expectedPositionOnMastery = 1;
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "checkORewards",
            "reshuffleAutomaActionDeck--123",
            "newClanMarker--123",
            "claimMC--123",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        //card is reshuffled
        assertSame(CARD_AUTOMA_LOCATION_DECK, TestDatas::$cards[201]['card_location']);
        assertSame(5, Globals::getAutomaScore());//+5
        assertSame(5, TestDatas::$stats['table'][14]);
        //Test new clan marker
        assertSame(44, TestDatas::$lastInsertedId);
        assertSame(['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => $expectedPositionOnMastery, 'meeple_location'=> "tile-$expectedMasteryId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ], TestDatas::$tokens[44],  );
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_beforeAutomaTurn_claimMasteries_AlreadyClaimed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setTurn(3);
        Globals::setAutomaDie(4);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_AUTOMA_ACTION) $card['card_location'] = CARD_AUTOMA_LOCATION_PLAYED;
        TestDatas::$cards[201]['card_location'] = CARD_AUTOMA_LOCATION_DECK;
        TestDatas::$tokens[25]['meeple_state']--; 
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-1",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> "tile-2",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> "tile-3",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ];
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "giveResource--123",
            "checkORewards",
            "reshuffleAutomaActionDeck--123",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        //card is reshuffled
        assertSame(CARD_AUTOMA_LOCATION_DECK, TestDatas::$cards[201]['card_location']);
        assertSame(0, Globals::getAutomaScore());
        assertSame(0, TestDatas::$stats['table'][14]);
        //Test NO new clan marker
        assertSame(1, TestDatas::$lastInsertedId);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
}