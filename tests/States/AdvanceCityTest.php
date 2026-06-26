<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\AdvanceCity;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Models\MAIN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class AdvanceCityTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_Region1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 1;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 1, 3, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 2;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 2, 4, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 3;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 5, 7, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 4;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 6, 8, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 5;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 9, 11, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 6;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 10, 12, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_CannotGoBackward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."3-1";
        TestDatas::$players[1]['die_face'] = 1;
        $expectedArgs = [
            'citySpaces' => [  ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_UsedSpaces(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[38]['meeple_location'] = MEEPLE_LOCATION_CITY."1-1";
        TestDatas::$players[1]['die_face'] = 1;
        $expectedArgs = [
            'citySpaces' => [ 37 => [  3, ], ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_EnteringState_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_actSelectAdvanceDest_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;

        $newState = $state->actSelectAdvanceDest($space,$markerId,999999, 1, $args);
        
        $expectedNotifs = [
            "moveCityMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    public function test_actSelectAdvanceDest_KO_WrongSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        $args = $state->getArgs();
        $space = 99;
        $markerId = 37;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid space $space");
        $state->actSelectAdvanceDest($space,$markerId,999999, 1, $args);
    }
    
    public function test_actSelectAdvanceDest_KO_UsedSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[38]['meeple_location'] = MEEPLE_LOCATION_CITY."1-1";
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid space $space");
        $state->actSelectAdvanceDest($space,$markerId,999999, 1, $args);
    }
    public function test_actSelectAdvanceDest_KO_WrongPlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid marker 37");
        $state->actSelectAdvanceDest($space,$markerId,999999, 1, $args);
    }
    // -------------------------------------------------
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $state->actRestart(999999,);
        
        assertSame(1, 1);
    }
    // -------------------------------------------------
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $state->actUndoToStep(1, 999999,);
        
        assertSame(1, 1);
    }
    
    // -------------------------------------------------
 
    public function test_Zombie_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}