<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\AutomaCards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\AutomaActionType;
use ROG\Models\ScenarioType;
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
    
    public function testEnteringState_ScenarioPhoenix1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => true,  ];
        GamestateMachine::$test_current_state = ST_NEXT_TURN;

        $game->stNextTurn();
        
        assertSame(0,  TestDatas::$cards[301]['card_played']);
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
        TestDatas::$cards[201]['card_state'] = 10;
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
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
        TestDatas::$cards[201]['card_state'] = 10;

        $game->stNextTurn();
        
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[201]['card_location']);
        assertSame(1, TestDatas::$cards[201]['card_state']);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    //play again with ADVANCE
    public function testEnteringState_beforeAutomaTurn_playAgain(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        Globals::setTurn(3);
        Globals::setAutomaDie(1);
        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        TestDatas::$test_activePlayerId = 2;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);

        $game->stNextTurn();
        
        $expectedNotifs = [
            "giveActionCardToAutoma-".AUTOMA_PLAYER_ID,
            "newClanMarker-".AUTOMA_PLAYER_ID,
            "moveCityMarker-".AUTOMA_PLAYER_ID,
            "discardCityCard-".AUTOMA_PLAYER_ID,
            //play again :
            "rollDie-".AUTOMA_PLAYER_ID,
            "giveActionCardToAutoma-".AUTOMA_PLAYER_ID,
            "build-".AUTOMA_PLAYER_ID,
            "newClanMarker-".AUTOMA_PLAYER_ID,
            "gainInfluence-".AUTOMA_PLAYER_ID,
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[209]['card_location']);
        assertSame(1, TestDatas::$cards[209]['card_state']);
        assertSame(CARD_AUTOMA_LOCATION_PLAYED, TestDatas::$cards[208]['card_location']);
        assertSame(2, TestDatas::$cards[208]['card_state']);
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
        
        assertSame("reshuffleAutomaActionDeck--123", TestDatas::$notifs['all'][0]);
        assertSame("giveActionCardToAutoma--123", TestDatas::$notifs['all'][1]);
        //We are not sure about next notifs...
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        $automaPlayedCards = AutomaCards::getInLocationOrdered(CARD_AUTOMA_LOCATION_PLAYED);
        $automaPlayedTypes = $automaPlayedCards->map(function($c) {return $c->getType();} )->toArray();
        if(in_array( AutomaActionType::ADVANCE_CITY->value,$automaPlayedTypes)){
            logForTests("Advance played after reshuffle : ".json_encode($automaPlayedTypes));
            assertSame(2, $automaPlayedCards->count());
        }
        else {
            logForTests("Advance NOT played after reshuffle : ".json_encode($automaPlayedTypes));
            assertSame(1, $automaPlayedCards->count());
        }
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
        //tiles not moved : 
        assertSame(3, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(0, Tiles::countInLocation(TILE_LOCATION_MASTERY_RESERVED));
        assertSame(1, Meeples::countPlayerMasteries(AUTOMA_PLAYER_ID));
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
        //tiles not moved : 
        assertSame(3, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(0, Tiles::countInLocation(TILE_LOCATION_MASTERY_RESERVED));
        assertSame(1, Meeples::countPlayerMasteries(AUTOMA_PLAYER_ID));
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
        //tiles not moved : 
        assertSame(3, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(0, Tiles::countInLocation(TILE_LOCATION_MASTERY_RESERVED));
        assertSame(2, Meeples::countPlayerMasteries(AUTOMA_PLAYER_ID));
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
        //tiles not moved : 
        assertSame(3, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(0, Tiles::countInLocation(TILE_LOCATION_MASTERY_RESERVED));
        assertSame(3, Meeples::countPlayerMasteries(AUTOMA_PLAYER_ID));
    }
    
    public function testEnteringState_beforeAutomaTurn_claimMasteries_ScenarioPhoenix(): void
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
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO,  ];
        TestDatas::$tiles[2]['tile_location'] = TILE_LOCATION_MASTERY_DECK;
        TestDatas::$tiles[3]['tile_location'] = TILE_LOCATION_MASTERY_DECK;
        $player = Players::automaPlayer();
        $expectedPositionOnMastery = 1;
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "checkORewards",
            "reshuffleAutomaActionDeck--123",
            "giveMasteriesTo--123",
            "newClanMarker--123",
            "claimMC--123",
            "masteryDeck",
            "giveMasteriesTo--123",
            "newClanMarker--123",
            "claimMC--123",
            "masteryDeck",
        ];

        $game->stNextTurn();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(AUTOMA_PLAYER_ID, Globals::getTurnPlayer());
        assertSame(true, Globals::isAutomaActive());
        //card is reshuffled
        assertSame(CARD_AUTOMA_LOCATION_DECK, TestDatas::$cards[201]['card_location']);
        assertSame(10, Globals::getAutomaScore());//+5*2
        assertSame(10, TestDatas::$stats['table'][14]);
        //Test new clan markers
        assertSame(44, TestDatas::$lastInsertedId);
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame($expectedPositionOnMastery, $newClanMarker['meeple_state']);
        assertSame(AUTOMA_PLAYER_ID, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        $newClanMarker = TestDatas::$tokens[44];
        assertSame(MEEPLE_LOCATION_TILE.'9', $newClanMarker['meeple_location']);
        assertSame($expectedPositionOnMastery, $newClanMarker['meeple_state']);
        assertSame(AUTOMA_PLAYER_ID, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
        //2 tiles reserved, 1 still on top of deck 
        assertSame(2, Tiles::getMasteryReserved($player)->count());
        assertSame(1, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(6, Tiles::countInLocation(TILE_LOCATION_MASTERY_DECK));
        assertSame(2, Meeples::countPlayerMasteries($player->getId()));
    }
    
    public function testEnteringState_beforeAutomaTurn_claimMasteries_ScenarioPhoenix_DeckEnd(): void
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
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO,  ];
        TestDatas::$tiles[2]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[3]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[4]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[5]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[6]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[7]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[8]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[9]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tiles[2]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[3]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[4]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[5]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[6]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[7]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[8]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[9]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tokens[42] = ['result_associative_index' => 42, 'meeple_id' => 42, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'4','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[46] = ['result_associative_index' => 46, 'meeple_id' => 46, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[47] = ['result_associative_index' => 47, 'meeple_id' => 47, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'7','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[48] = ['result_associative_index' => 48, 'meeple_id' => 48, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'8','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        TestDatas::$tokens[49] = ['result_associative_index' => 49, 'meeple_id' => 49, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'9','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ];
        $player = Players::automaPlayer();
        $expectedPositionOnMastery = 1;
        $expectedNotifs = [
            "giveActionCardToAutoma--123",
            "sail--123",
            "checkVRewards",
            "checkORewards",
            "reshuffleAutomaActionDeck--123",
            "giveMasteriesTo--123",
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
        //Test new clan markers
        assertSame(50, TestDatas::$lastInsertedId);
        $newClanMarker = TestDatas::$tokens[TestDatas::$lastInsertedId];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame($expectedPositionOnMastery, $newClanMarker['meeple_state']);
        assertSame(AUTOMA_PLAYER_ID, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        //8 + 1 tiles reserved
        assertSame(9, Tiles::getMasteryReserved($player)->count());
        assertSame(0, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(0, Tiles::countInLocation(TILE_LOCATION_MASTERY_DECK));
        assertSame(9, Meeples::countPlayerMasteries($player->getId()));
    }
}