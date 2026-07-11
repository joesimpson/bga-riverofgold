<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusSelectRegion;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Managers\Meeples;
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
            'nbr' => 1,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    //To test reward after BONUS_TYPE_BUILDING_ROW_REWARDS
    public function test_Args_influenceReward_AnyRegion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_INF_SELECT_REGION;
        $currentBonusDatas = ['region'=>0,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            'p' => [1,2,3,4,5,6],
            'nbr' => 1,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_MultiRewards(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        $currentBonus = BONUS_TYPE_REWARDS_SELECT_REGION;
        $currentBonusDatas = ['bonusQuantity'=>2,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            'p' => [1,2,3,4,5,6],
            'nbr' => 2,
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
 
    public function test_ActionSelectRegion_Pass_3(): void
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
        $choice = [3];

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
    public function test_ActionSelectRegion_Pass_6(): void
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
        $choice = [6];

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
    public function test_ActionSelectRegion_KO_WrongChoiceCount(): void
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
        $choice = [3,4];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You need to select 1 choices");
        $state->actSelectRegion($choice,999999, 1, $args);
    }
    public function test_ActionSelectRegion_KO_WrongChoice(): void
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
        $choice = [3];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid choice 3");
        $state->actSelectRegion($choice,999999, 1, $args);
    }
    // -------------------------------------------------
    
    public function test_ActionSelectMultiRegions_Pass_3_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusSelectRegion($game);
        TestDatas::$players[1]['player_score'] = 27;
        $currentBonus = BONUS_TYPE_REWARDS_SELECT_REGION;
        $currentBonusDatas = ['bonusQuantity'=>2,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$tokens[1]['meeple_state'] = 6;
        TestDatas::$tokens[2]['meeple_state'] = 6;
        TestDatas::$tokens[3]['meeple_state'] = 6;
        TestDatas::$tokens[4]['meeple_state'] = 6;
        TestDatas::$tokens[5]['meeple_state'] = 14;
        TestDatas::$tokens[6]['meeple_state'] = 6;
        TestDatas::$tiles[2]['type'] = 15;//MASTERY_TYPE_LIGHTNING
        $args = $state->getArgs();
        $choice = [3,5];

        $newState = $state->actSelectRegion($choice,999999, 1, $args);
        
        $expectedNotifs = [
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "giveResource-1",
            "addPoints-1",
            "newClanMarker-1",
            "claimMC-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);//+1
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);//+1
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(4, $resources[RESOURCE_TYPE_MONEY]);//+2*2
        assertSame(35, TestDatas::$players[1]['player_score']);//+3 +5
        assertSame(1, Meeples::countPlayerMasteries(1));
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'2', $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
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