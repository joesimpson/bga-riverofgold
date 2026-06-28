<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusFreeBuild;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Helpers\Collection;
use ROG\Managers\ShoreSpaces;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusFreeBuildTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);
        $currentBonus = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::resetStartingBuildings();
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        $expectedSpaces = new Collection( [ 
                //ships on 5,14
                ShoreSpaces::getShoreSpace(9),
                ShoreSpaces::getShoreSpace(10),
                //ShoreSpaces::getShoreSpace(11),
                ShoreSpaces::getShoreSpace(12),
                ShoreSpaces::getShoreSpace(26),
                ShoreSpaces::getShoreSpace(28),
                ShoreSpaces::getShoreSpace(30),
             ]);
        foreach($expectedSpaces as &$s) {
            $s->cost = 0;
        }
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            'spaces' => $expectedSpaces, 
            'tiles' => [31,32,33,34],
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
        $state = new BonusFreeBuild($game);
        $currentBonus = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_actFreeBuild_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);
        $currentBonus = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 31;
        $position = 9;

        $newState = $state->actFreeBuild($position, $tileId,999999, 1, $args);
        
        $expectedNotifs = [
            "spendMoney-1",//with 0
            "build-1",
            "newClanMarker-1",
            "gainInfluence-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$tileId]['tile_location']);
        assertSame($position, TestDatas::$tiles[$tileId]['tile_state']);
        //Test new clan marker
        assertSame(TestDatas::$tokens[999], ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => 1, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        assertSame(0, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsBuild']);
        //Test gained influence :
        assertSame(1, TestDatas::$tokens[2]['meeple_state']);
    }

    public function test_actFreeBuild_KO_WrongSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);
        $currentBonus = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 31;
        $position = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build on $position");
        $state->actFreeBuild($position, $tileId,999999, 1, $args);
    } 
    public function test_actFreeBuild_KO_WrongTile(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);
        $currentBonus = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $tileId = 456;
        $position = 9;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build tile $tileId");
        $state->actFreeBuild($position, $tileId,999999, 1, $args);
    } 
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);
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
        $state = new BonusFreeBuild($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusFreeBuild($game);
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
        $state = new BonusFreeBuild($game);
        $currentBonus = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}