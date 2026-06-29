<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusFreeSail;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Helpers\Collection;
use ROG\Managers\ShoreSpaces;
use ROG\Models\MAIN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusFreeSailTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$tokens[21]['meeple_state'] = 2;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            'spaces' => [
                //['shipId' => positions]
                21 => [1,  3,4,5,6,7,8,9,10,11,12,13,14 ],
                22 => [1,2,3,4,5,6,7,8,9,   11,12,13,14 ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertEquals($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_EnteringState_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_actFreeSail_Pass_Upriver(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        Globals::setTurnMainActionDone(MAIN_ACTION::ADVANCE->value);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":0,"6":0}';
        TestDatas::$tokens[21]['meeple_state'] = 2;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        $args = $state->getArgs();
        $shipId = 22;
        $riverSpace = 1;

        $newState = $state->actFreeSail($shipId, $riverSpace,999999, 1, $args);
        
        $expectedNotifs = [
            "sail-1",
            //WE Shall not complete the journey with 'reachRiverEnd'
            "checkVRewards",
            "giveResource-1", 
            "giveResource-1", 
            "giveResource-1", 
            "giveResource-1", // space 41 [RESOURCE_TYPE_MONEY=>3]
            "checkORewards",
            "giveResource-1",  //space 41 [BONUS_TYPE_MONEY_PER_PORT=>1]
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN ]);
        assertSame(7, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*3 +1 +3
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(0, TestDatas::$stats[1]['nbActionsSail']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_actFreeSail_Pass_Downriver(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        Globals::setTurnMainActionDone(MAIN_ACTION::ADVANCE->value);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":0,"6":0}';
        TestDatas::$tokens[21]['meeple_state'] = 2;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        $args = $state->getArgs();
        $shipId = 21;
        $riverSpace = 14;

        $newState = $state->actFreeSail($shipId, $riverSpace,999999, 1, $args);
        
        $expectedNotifs = [
            "sail-1",
            //WE Shall not complete the journey with 'reachRiverEnd'
            "checkVRewards",
            "giveResource-1", 
            "giveResource-1", 
            "giveResource-1", 
            "addBonus-1",    // space 29 [RESOURCE_TYPE_SILK=>1,BONUS_TYPE_DRAW =>1]
            "giveResource-1", 
            "checkORewards",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK ]);//+1
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN ]);
        assertSame(3, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*3
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(0, TestDatas::$stats[1]['nbActionsSail']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }

    public function test_actFreeSail_KO_WrongSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$tokens[21]['meeple_state'] = 2;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        $args = $state->getArgs();
        $shipId = 21;
        $riverSpace = 2;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Sail to $riverSpace");
        $newState = $state->actFreeSail($shipId, $riverSpace,999999, 1, $args);
    } 
    public function test_actFreeSail_KO_WrongShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$tokens[21]['meeple_state'] = 2;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        $args = $state->getArgs();
        $shipId = 111;
        $riverSpace = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Sail ship $shipId");
        $newState = $state->actFreeSail($shipId, $riverSpace,999999, 1, $args);
    } 
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
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
        $state = new BonusFreeSail($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeSail($game);
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
        $state = new BonusFreeSail($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}