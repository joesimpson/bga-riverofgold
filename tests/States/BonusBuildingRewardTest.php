<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusBuildingReward;
use ROG\Models\BonusBuildingRewardChoice;
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
    public function test_Args_BuildingReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setChoices(0);
        Globals::setLastBuiltTile(41);
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $expectedArgs = [
            'c' => BONUS_TYPE_BUILDING_REWARD,
            'p' => [
                41 => [
                    'type' => 5,
                    'choices' => [
                        BonusBuildingRewardChoice::OWNER->value,
                        BonusBuildingRewardChoice::VISITOR->value,
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_AnyOwnerReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ANY_OWNER_REWARD);
        $expectedArgs = [
            'c' => BONUS_TYPE_ANY_OWNER_REWARD,
            'p' => [
                41 => [
                    'type' => 5,
                    'choices' => [
                        BonusBuildingRewardChoice::OWNER->value,
                    ],
                ],
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
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
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
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = BonusBuildingRewardChoice::OWNER->value;//OWner

        $newState = $state->actSelectReward($choice, $tileId,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionSelectReward_Pass_VisitorReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = BonusBuildingRewardChoice::VISITOR->value;//Visitor

        $newState = $state->actSelectReward($choice, $tileId,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionSelectReward_Pass_OwnerReward_x2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 41;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_state'] = 2;
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        $choice = BonusBuildingRewardChoice::OWNER->value;//OWner

        $newState = $state->actSelectReward($choice, $tileId,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
    }

    public function test_ActionSelectReward_KO_WrongTile(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 456;
        $choice = BonusBuildingRewardChoice::OWNER->value;

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
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = 3;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid choice $choice");
        $state->actSelectReward($choice, $tileId,999999, 1, $args);
    }
    public function test_ActionSelectReward_KO_WrongChoiceVisitor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusBuildingReward($game);
        Globals::setLastBuiltTile(41);
        Globals::setCurrentBonus(BONUS_TYPE_ANY_OWNER_REWARD);
        $args = $state->getArgs();
        $tileId = 41;
        $choice = BonusBuildingRewardChoice::VISITOR->value;

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
        Globals::setLastBuiltTile(41);
        $currentBonus = BONUS_TYPE_BUILDING_REWARD;
        $currentBonusDatas = ['tile'=>41,'position'=>5,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}