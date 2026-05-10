<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use Bga\Games\RiverOfGoldNightMarket\States\BonusPlaceLion;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusPlaceLionTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
        Globals::setChoices(0);
        $expectedArgs = [
            'spaces' => [
                1,2,3, 6,7,8,9,10,
                11,12,13,14,15,16,17,18,19,20,
                21,22,23,24,25,26,27,28,29,30

            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Empty(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
        Globals::setChoices(0);
        for($k = 0; $k<30;$k++){
            //Fill all shore spaces
            $index = 41+$k;
            TestDatas::$tiles[$index] = TestDatas::$tiles[41];
            TestDatas::$tiles[$index]['tile_id'] = $index;
            TestDatas::$tiles[$index]['result_associative_index'] = $index;
            TestDatas::$tiles[$index]['tile_state'] = $k +1;
        }
        $expectedArgs = [
            'spaces' => [],
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
        $state = new BonusPlaceLion($game);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_ActionPlaceLion_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
        $shore_space = 2;
        $args = $state->getArgs();

        $newState = $state->actPlaceLion($shore_space, 999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_SHORE."$shore_space", $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_LION_MARKER, $newClanMarker['type']);
    }
    
    public function test_ActionPlaceLion_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
        $shore_space = 4;
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid space $shore_space");
        $state->actPlaceLion($shore_space , 999999, 1, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $state->actRestart(999999,);
        
        assertSame(1, 1);
    }
    // -------------------------------------------------
 
    public function test_ActionUndo_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
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
        $state = new BonusPlaceLion($game);
        $args = $state->getArgs();
        $shore_space = 1;

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_SHORE."$shore_space", $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_LION_MARKER, $newClanMarker['type']);
    }
    
    public function test_Zombie_Pass_Empty(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPlaceLion($game);
        for($k = 0; $k<30;$k++){
            //Fill all shore spaces
            $index = 41+$k;
            TestDatas::$tiles[$index] = TestDatas::$tiles[41];
            TestDatas::$tiles[$index]['tile_id'] = $index;
            TestDatas::$tiles[$index]['result_associative_index'] = $index;
            TestDatas::$tiles[$index]['tile_state'] = $k +1;
        }
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame([], $args['spaces']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertFalse( array_key_exists(43,TestDatas::$tokens));//no new clan marker
    }
    
    // -------------------------------------------------
}