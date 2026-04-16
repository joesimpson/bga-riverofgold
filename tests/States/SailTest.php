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
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[34]['tile_location']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(7, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*3 + 1 as owner reward + 3 as visitor reward
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
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