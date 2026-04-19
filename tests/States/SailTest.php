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

final class SailTest extends TestCase
{

    // -------------------------------------------------
    // -------------------------------------------------
    
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [6],
                22 => [1],//14 +1 = 1
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }

    public function test_Args_Noble5_ship1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 2;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [7, 8,6],
                22 => [
                    2, //14 +2 = 2
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Noble5_ship2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$tokens[22]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [6],
                22 => [
                    1, //14 +1 = 1
                    2, //14 +1 +1 = 2
                    6, //14 +1 +6 = 6
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    
    // -------------------------------------------------
    public function test_ActionSail_Pass_Ship1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 21;
        $riverSpace = 6;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(4, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*4
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[34]['tile_location']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(7, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*3 + 1 as owner reward + 3 as visitor reward
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
    }
    
    public function test_ActionSail_Pass_Merchant1_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        //check influences : +1 in owned region
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_Merchant2_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_2;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(19+1, TestDatas::$players[TestDatas::$test_activePlayerId]['player_score']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Merchant3_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_3;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, BONUS_TYPE_CHOICE]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Merchant4_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_4;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, BONUS_TYPE_SELL_GOODS]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Merchant5_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_5;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Merchant6_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_6;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(3 + 7, $resources[RESOURCE_TYPE_MONEY]);//3 + sail base( EMPTY_SPACE_REWARD*3 + 1 as owner reward + 3 as visitor reward )
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Monk1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MONK_1;
        TestDatas::$tiles[41]['tile_state'] = 11;
        TestDatas::$tiles[42]['tile_state'] = 12;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_state'] = 2;
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        $shipId = 21;
        $riverSpace = 6;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(10, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*2 + 1*2 as owner reward + 3 as Own visitor reward + 3 as Opponent visitor reward
        assertSame(19+0,TestDatas::$players[1]['player_score']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Monk2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MONK_2;
        TestDatas::$tiles[41]['tile_state'] = 11;
        TestDatas::$tiles[42]['tile_state'] = 12;
        TestDatas::$tiles[42]['type'] = 9;
        TestDatas::$tokens[43] = TestDatas::$tokens[42];
        TestDatas::$tokens[43]['meeple_state'] = 2;
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['player_id'] = 1;
        $shipId = 21;
        $riverSpace = 6;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(6, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*2 + 1 as owner reward + 3 as Own visitor reward 
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);//+1 as owner reward on oppponent tile
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);//1 as visitor reward on oppponent tile
        assertSame(20,TestDatas::$players[1]['player_score']);//+1 as owner reward on Opponent tile
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Noble1_WithStandardShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        //market with good:
        TestDatas::$tiles[40] = ['result_associative_index' => 40,'tile_id' => 40, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 3,  'type' => 8, 'subtype' => TILE_TYPE_BUILDING, ];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        $riverSpace = 2;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble1_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        //market with good:
        TestDatas::$tiles[40] = ['result_associative_index' => 40,'tile_id' => 40, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 3,  'type' => 8, 'subtype' => TILE_TYPE_BUILDING, ];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        $riverSpace = 2;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([BONUS_TYPE_CHOICE, ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble2_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_2;
        $shipId = 21;
        $riverSpace = 6;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace);
        
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(8, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*4 *2
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble3_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_3;
        $shipId = 21;
        $riverSpace = 5;
        TestDatas::$tokens[$shipId]['meeple_state'] = 4;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(1, TestDatas::$tokens[2]['meeple_state']);
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble4_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_4;
        $shipId = 21;
        $riverSpace = 2;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame(19+1,TestDatas::$players[1]['player_score']);//+NB_POINTS_NOBLE_4
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble6_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_6;
        $shipId = 21;
        $riverSpace = 2;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame(19+1,TestDatas::$players[1]['player_score']);//+NB_POINTS_NOBLE_6
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }

    public function test_ActionSail_Pass_IronCrane(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_IRON_CRANE;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        $riverSpace = 2;

        $game->actSailSelect($shipId,$riverSpace);
        
        assertSame(19+2,TestDatas::$players[1]['player_score']);//+NB_POINTS_IRON_CRANE
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_KO_WrongShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 1;
        $riverSpace = 6;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Sail ship $shipId");
        $game->actSailSelect($shipId,$riverSpace);
    }
    public function test_ActionSail_KO_WrongRiverSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 21;
        $riverSpace = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Sail to $riverSpace");
        $game->actSailSelect($shipId,$riverSpace);
    }
    // -------------------------------------------------
}