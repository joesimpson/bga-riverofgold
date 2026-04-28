<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusBuildingReward;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusBuildingRewardTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setChoices(0);
        Globals::setLastBuiltTile(41);
        $expectedArgs = [
            'p' => [
                41 => [
                    1,2,
                ]
            ],
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
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_ActionSelectReward_Pass_OwnerReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = 1;//OWner

        $newState = $state->actSelectReward($choice, $tileId,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    public function test_ActionSelectReward_Pass_VisitorReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = 2;//Visitor

        $newState = $state->actSelectReward($choice, $tileId,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    public function test_ActionSelectReward_KO_WrongTile(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $args = $state->getArgs();
        $tileId = 456;
        $choice = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid tile $tileId");
        $state->actSelectReward($choice, $tileId,999999, 1, $args);
    }
    public function test_ActionSelectReward_KO_WrongChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = 3;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid choice $choice");
        $state->actSelectReward($choice, $tileId,999999, 1, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
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
        $state = new BonusBuildingReward($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
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
        $state = new BonusBuildingReward($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}