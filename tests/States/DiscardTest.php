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

        $game->actDiscardCard($cardId);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DISCARD, $cardDatas['card_location']);
        assertSame(0, $cardDatas['player_id']);//null becomes '0' with Model
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
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