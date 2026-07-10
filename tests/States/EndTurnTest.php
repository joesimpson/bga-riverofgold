<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Models\MAIN_ACTION;
use ROG\Models\ScenarioType;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertFalse;

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
        assertSame(ST_END_TURN, Globals::getStateBeforeBonus());
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
    
    public function test_EnteringState_EmperorVisitWithBonuses_WhenAutomaBuilds(): void
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
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaActive(true);
        Globals::setTurnPlayer(AUTOMA_PLAYER_ID);

        $game->stEndTurn();
        
        assertSame(2, Globals::getEra());
        $expectedBonuses = json_encode([BONUS_TYPE_CHOICE]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(1, TestDatas::$test_activePlayerId);
    }

    public function test_runEmperorVisit_WithoutReverendSensei(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        //add a second building tile  + 2 clan markers on it
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['type'] = 3;
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tokens[44] = TestDatas::$tokens[43];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_state'] = 2;

        $game->runEmperorVisit();
        
        assertSame(2, Globals::getEra());
        //Check PLAYER 1 owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_SILK]);
        assertSame(2, $resourcesPlayer1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_RICE]);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_SUN]);
        assertSame(4, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);//0+1*2+2
        assertFalse( array_key_exists(45,TestDatas::$tokens));//no new clan marker
        //Check PLAYER 2 owner rewards :
        assertSame(2 + 1, TestDatas::$players[2]['player_score']);
    }
    public function test_runEmperorVisit_WithReverendSensei(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        TestDatas::$cards[101]['type'] = PATRON_REVEREND_SENSEI;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //add a second building tile  + 2 clan markers on it
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['type'] = 3;
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tokens[44] = TestDatas::$tokens[43];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_state'] = 2;

        $game->runEmperorVisit();
        
        assertSame(2, Globals::getEra());
        //Check PLAYER 1 owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_SILK]);
        assertSame(2, $resourcesPlayer1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_RICE]);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_SUN]);
        assertSame(6, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);//0+1*2+2*2
        $newClanMarker = TestDatas::$tokens[45];
        assertSame(MEEPLE_LOCATION_TILE.'41', $newClanMarker['meeple_location']);
        assertSame(2, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        //Check PLAYER 2 owner rewards :
        assertSame(2 + 1, TestDatas::$players[2]['player_score']);
    }
    
    public function test_runEmperorVisit_WithAutomaOwnBuildings(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        TestDatas::$tokens[42]['player_id'] = AUTOMA_PLAYER_ID;
        //add a second building tile  + 2 clan markers on it
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['type'] = 3;
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 7;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tokens[44] = TestDatas::$tokens[43];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_state'] = 2;
        TestDatas::$tokens[44]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "emperorVisit",
            "emperorReward",//tile 41 [BONUS_TYPE_MONEY_PER_PORT=>1],
            "giveResource-1",
            "emperorReward",//tile 42 [BONUS_TYPE_POINTS=>1],
            "addPoints--123",
            "emperorReward",//tile 43 [RESOURCE_TYPE_MONEY=>1, RESOURCE_TYPE_POTTERY=>1,],
            "giveResource-1",
            "giveResource-1",
            "emperorVisitEnd",
        ];

        $game->runEmperorVisit();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(2, Globals::getEra());
        //Check PLAYER 1 owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_SILK]);
        assertSame(1, $resourcesPlayer1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_RICE]);
        assertSame(3, $resourcesPlayer1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_SUN]);
        assertSame(3, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);//0+1*2+1
        //Check PLAYER 2 owner rewards :
        assertSame(2, TestDatas::$players[2]['player_score']); //+0
        $resourcesPlayer2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesPlayer2[RESOURCE_TYPE_SILK]);
        assertSame(0, $resourcesPlayer2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesPlayer2[RESOURCE_TYPE_RICE]);
        assertSame(3, $resourcesPlayer2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesPlayer2[RESOURCE_TYPE_SUN]);
        assertSame(0, $resourcesPlayer2[RESOURCE_TYPE_MONEY]);
        assertSame(1, Globals::getAutomaScore());
        assertSame(1, TestDatas::$stats['table'][14]);
    }
    
    public function test_EnteringState_EmperorVisit_AfterShindoshi3_Pass(): void
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
        Globals::setLastBuiltTile(26);
        Globals::setLastBuiltLocationOrigin(TILE_LOCATION_BUILDING_DECK_ERA_1);
        Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);
        Globals::setEra(1);

        $game->stEndTurn();
        
        assertSame(2, Globals::getEra());
        //Check owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);
        assertSame(2 + 1, TestDatas::$players[2]['player_score']);
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_EnteringState_EmperorVisit_AfterShindoshi3_NotEmptyStack_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        Globals::setLastBuiltTile(25);
        Globals::setLastBuiltLocationOrigin(TILE_LOCATION_BUILDING_DECK_ERA_1);
        Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);
        Globals::setEra(1);

        $game->stEndTurn();
        
        assertSame(1, Globals::getEra());
        //Check NO owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }

    public function test_EnteringState_EmperorVisit_AfterShindoshi3_Later_KO(): void
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
        Globals::setLastBuiltTile(26);
        Globals::setLastBuiltLocationOrigin(TILE_LOCATION_BUILDING_DECK_ERA_1);
        Globals::setTurnMainActionDone(MAIN_ACTION::SAIL->value);
        Globals::setEra(1);

        $game->stEndTurn();
        
        assertSame(1, Globals::getEra());
        //Check NO owner rewards :
        $resourcesPlayer1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesPlayer1[RESOURCE_TYPE_MONEY]);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_runEmperorVisit_ScenarioLion1_Defeat(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::LION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //assassins
        TestDatas::$tokens[43]= ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[44]= ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[45]= ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."5",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        
        $halt = $game->runEmperorVisit();
        
        $expectedNotifs = [
            "emperorVisit",
            "emperorReward",//tile 41 [BONUS_TYPE_MONEY_PER_PORT=>1],
            "giveResource-1",
            "emperorReward",//tile 42 [BONUS_TYPE_POINTS=>1],
            "addPoints-2",
            "emperorVisitEnd",
            "emperorVisitDefeat-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(true, $halt );
    }
    public function test_runEmperorVisit_ScenarioLion1_NoDefeat(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::LION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //assassins
        TestDatas::$tokens[43]= ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[44]= ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        
        $halt = $game->runEmperorVisit();
        
        $expectedNotifs = [
            "emperorVisit",
            "emperorReward",//tile 41 [BONUS_TYPE_MONEY_PER_PORT=>1],
            "giveResource-1",
            "emperorReward",//tile 42 [BONUS_TYPE_POINTS=>1],
            "addPoints-2",
            "emperorVisitEnd",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(false, $halt );
    }
    
    public function test_EnteringState_EmperorVisit_ScenarioLion1_Defeat(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::LION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //assassins
        TestDatas::$tokens[43]= ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[44]= ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[45]= ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."5",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[34]);
        
        $game->stEndTurn();
        
        assertSame(true, TestDatas::$players[1]['last_turn_played']);
        assertSame(true, TestDatas::$players[2]['last_turn_played']);
        assertSame(true, Globals::isAutomaLastTurnPlayed());
        assertSame(1, Globals::getEndPlayer());
        assertSame(true, Globals::isLastTurnTriggered());
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_EnteringState_EmperorVisit_ScenarioLion1_NoDefeat(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_TURN;
        Globals::setEra(1);
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::LION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //assassins
        TestDatas::$tokens[43]= ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[44]= ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[34]);

        $game->stEndTurn();
        
        assertSame(false, TestDatas::$players[1]['last_turn_played']);
        assertSame(false, TestDatas::$players[2]['last_turn_played']);
        assertSame(false, Globals::isAutomaLastTurnPlayed());
        assertSame(0, Globals::getEndPlayer());
        assertSame(false, Globals::isLastTurnTriggered());
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
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

    
    public function test_EnteringState_LastTurn_Shindoshi3_Pass(): void
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
        Globals::setLastBuiltTile(106);
        Globals::setLastBuiltLocationOrigin(TILE_LOCATION_BUILDING_DECK_ERA_2);
        Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);
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
    
    public function test_EnteringState_LastTurn_Shindoshi3_NotEmptyStack_KO(): void
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
        Globals::setLastBuiltTile(104);
        Globals::setLastBuiltLocationOrigin(TILE_LOCATION_BUILDING_DECK_ERA_2);
        Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);

        $game->stEndTurn();
        
        //Score +0
        assertSame(19, TestDatas::$players[1]['player_score']);
        assertSame(false, TestDatas::$players[1]['last_turn_played']);
        assertSame(0, Globals::getEndPlayer());
        assertSame(false, Globals::isLastTurnTriggered());
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_EnteringState_LastTurn_ByAutomaBuild(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurnPlayer(AUTOMA_PLAYER_ID);
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
        Globals::setAutomaDie(-1);
        Globals::setAutomaScore(29);
        TestDatas::$stats['table'][14] = 29;
        TestDatas::$tiles[1]['type'] = 18;//MASTERY_TYPE_LIGHTNING
        $expectedNotifs = [
            "endTurn--123",
            "slideBuildingRow",
            "refillBuildingRow",
            "triggerLastTurn--123",
            "addPoints--123", 
            "revealTopEraTiles",
        ];

        $game->stEndTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //Score +NB_POINTS_FOR_GAME_END
        //Masteries SHOULD NOT BE TRIGGERED this way FOR AUTOMA : (+7 when mastery claimed)
        assertSame(34, Globals::getAutomaScore());
        assertSame(34, TestDatas::$stats['table'][14]);
        assertSame(19, TestDatas::$players[1]['player_score']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        //check die NOT rolled :
        assertSame(-1, Globals::getAutomaDie());
        assertSame(false, TestDatas::$players[1]['last_turn_played']);
        assertSame(false, TestDatas::$players[2]['last_turn_played']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getEndPlayer());
        assertSame(true, Globals::isLastTurnTriggered());
        assertSame(true, Globals::isAutomaLastTurnPlayed());
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
}