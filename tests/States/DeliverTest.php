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

final class DeliverTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":5}';
        Globals::setChoices(0);
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [ 13, ], // cards ids
                ],

            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionDeliver_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        TestDatas::$tokens[3]['meeple_state'] = 2;

        $game->actDeliverSelect($cardId);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);// -2 Silk 
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resources[RESOURCE_TYPE_RICE]);
        assertSame(0, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
         //Test gained influence in region 3
        assertSame(4, TestDatas::$tokens[3]['meeple_state']);
        assertSame(json_encode([BONUS_TYPE_REFILL_HAND]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsDeliver']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionDeliver_KO_WrongLocation(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver this card");
        $game->actDeliverSelect($cardId);
    }
    public function test_ActionDeliver_KO_NotEnoughResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":1,"2":0,"3":2,"4":0,"5":0,"6":5}';

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId);
    }
    public function test_ActionDeliver_KO_WrongRegion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 11;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":2,"3":2,"4":0,"5":0,"6":0}';

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId);
    }

    public function test_ActionDeliver_Pass_AnyRegionWithSonOfStorm(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        TestDatas::$cards[101]['type'] = PATRON_SON_OF_STORM;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $cardId = 11;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":2,"3":2,"4":0,"5":0,"6":0}';

        //Test 1 OK with Son of storm 
        $game->actDeliverSelect($cardId);
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);

        //Test 2 KO without
        $game = new GameMock();
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId);
    }
    
    public function test_ActionDeliver_Pass_PRIESTESS(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        TestDatas::$cards[101]['type'] = PATRON_PRIESTESS;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $cardId = 13;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        
        $game->actDeliverSelect($cardId);

        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        //Test score +NB_POINTS_PRIESTESS
        assertSame(22, TestDatas::$players[TestDatas::$test_activePlayerId]['player_score']);

    }
    
    // -------------------------------------------------
}