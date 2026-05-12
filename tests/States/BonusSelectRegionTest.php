<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusSelectRegion;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusSelectRegionTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_influenceReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        Globals::setChoices(0);
        $region = 3;
        $currentBonus = BONUS_TYPE_INF_SELECT_REGION;
        $currentBonusDatas = ['region'=>$region,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            'p' => [1,2,4,5,6],
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
        $state = new BonusSelectRegion($game);
        $region = 3;
        $currentBonus = BONUS_TYPE_INF_SELECT_REGION;
        $currentBonusDatas = ['region'=>$region,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_ActionSelectRegion_3_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        $region = 5;
        $currentBonus = BONUS_TYPE_INF_SELECT_REGION;
        $currentBonusDatas = ['region'=>$region,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $choice = 3;

        $newState = $state->actSelectRegion($choice,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        //check influences
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    public function test_ActionSelectRegion_6_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        $region = 5;
        $currentBonus = BONUS_TYPE_INF_SELECT_REGION;
        $currentBonusDatas = ['region'=>$region,'bonusQuantity'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $choice = 6;

        $newState = $state->actSelectRegion($choice,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        //check influences
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);
    }
    public function test_ActionSelectRegion_3_KO_WrongChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        $region = 3;
        $currentBonus = BONUS_TYPE_INF_SELECT_REGION;
        $currentBonusDatas = ['region'=>$region,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $choice = 3;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid choice $choice");
        $state->actSelectRegion($choice,999999, 1, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
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
        $state = new BonusSelectRegion($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
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
        $state = new BonusSelectRegion($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}