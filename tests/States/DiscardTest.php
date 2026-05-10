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

final class DiscardTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        Globals::setChoices(0);
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [ 11, 13, ], // cards ids
                ],

            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDiscardCard();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionDiscardCard_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_DISCARD_CARD;
        $cardId = 13;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->actDiscardCard($cardId);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DISCARD, $cardDatas['card_location']);
        assertSame(0, $cardDatas['player_id']);//null becomes '0' with Model
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionDiscardCard_Pass_TatooedMonk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_DISCARD_CARD;
        $cardId = 13;
        TestDatas::$cards[101]['type'] = PATRON_TATTOOED_MONK;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->actDiscardCard($cardId);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);// no more silk gained
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        //Test gained influence in region 3 : +1
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionDiscardCard_KO_WrongLocation(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_DISCARD_CARD;
        $cardId = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot discard this card");
        $game->actDiscardCard($cardId);
    }
    
    // -------------------------------------------------
}