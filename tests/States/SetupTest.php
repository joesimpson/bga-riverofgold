<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Cards;
use ROG\Models\CustomerCard;
use Tests\Utils\PHPUnitUtil;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

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
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        
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
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
    }

    public function test_setupNewGame_Draft_5players(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        TestDatas::$players[5] = TestDatas::$players[1];
        TestDatas::$players[5]['player_id'] = 5;
        TestDatas::$players[5]['result_associative_index'] = 5;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
            3 => TestDatas::$players[3],
            4 => TestDatas::$players[4],
            5 => TestDatas::$players[5],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_DRAFT,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
    }
    
    public function test_setupNewGame_Customers_Base(): void
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
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        TestDatas::$cards = [];
        $expectedCustomerTypes = [1,2,3,4,5];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        //We need 6*5 cards
        assertSame(30, count(TestDatas::$cards));
        foreach(TestDatas::$cards as $cardRow){
            $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardRow['type']]);
            $cType = $card->getCustomerType();
            assertTrue(in_array($card->getCustomerType(),$expectedCustomerTypes), "Customer type $cType must be in ".json_encode($expectedCustomerTypes));
        }
    }
    
    public function test_setupNewGame_Customers_IntoCity(): void
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
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_INTOCITY,
        ];
        TestDatas::$cards = [];
        $expectedCustomerTypes = [CUSTOMER_TYPE_ELDER,CUSTOMER_TYPE_MERCHANT,CUSTOMER_TYPE_MONK,CUSTOMER_TYPE_NOBLE,CUSTOMER_TYPE_MAGISTRATE,CUSTOMER_TYPE_SPY];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        //We need 6*6 cards
        assertSame(36, count(TestDatas::$cards));
        foreach(TestDatas::$cards as $cardRow){
            $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardRow['type']]);
            $cType = $card->getCustomerType();
            assertTrue(in_array($card->getCustomerType(),$expectedCustomerTypes), "Customer type $cType must be in ".json_encode($expectedCustomerTypes));
            assertSame(CARD_LOCATION_DECK,$card->getLocation());
        }
        
    }
    
    public function test_setupNewGame_Customers_Random(): void
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
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_INTOCITY,
        ];
        TestDatas::$cards = [];
        $expectedCustomerTypes = ALL_CUSTOMER_TYPES;
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        //We need 6*6 cards
        assertSame(36, count(TestDatas::$cards));
        foreach(TestDatas::$cards as $cardRow){
            $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardRow['type']]);
            $cType = $card->getCustomerType();
            assertTrue(in_array($card->getCustomerType(),$expectedCustomerTypes), "Customer type $cType must be in ".json_encode($expectedCustomerTypes));
            assertSame(CARD_LOCATION_DECK,$card->getLocation());
        }
        
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
    
    public function testEnteringState_PlayerSetup_PatronMagnateSandRoad(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$cards[101]['type'] = PATRON_MAGNATE_SAND_ROAD ;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        //Test 3 ships
        assertSame(1,TestDatas::$tokens[49]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[49]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP,TestDatas::$tokens[49]['type']);
        assertSame(1,TestDatas::$tokens[50]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[50]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP,TestDatas::$tokens[50]['type']);
        assertSame(1,TestDatas::$tokens[51]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[51]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP_ROYAL,TestDatas::$tokens[51]['type']);
        assertSame(1,TestDatas::$tokens[51]['meeple_state']);
        
    }
    // ----------------------------------------------------------------------
    
    
}