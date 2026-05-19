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

final class BonusSecondMarkerTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    public function test_Args_Empty(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
        unset(TestDatas::$tokens[41]);
        $expectedArgs = [
            'p' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusSecondMarker();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_OwnBuildings(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
        $expectedArgs = [
            'p' => [ 41, ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusSecondMarker();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_OpponentBuildings(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);
        $expectedArgs = [
            'p' => [ 42, ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusSecondMarker();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
    
    // -------------------------------------------------
    
    public function test_ActionBonusSecondMarker_Pass_OwnBuilding(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        $tileId = 41;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_BUILDING);

        $game->actBonusSecondMarker($tileId,999999);
        
        $newTokenId = 43;
        assertSame(TestDatas::$tokens[$newTokenId], ['result_associative_index' => $newTokenId, 'meeple_id' => $newTokenId, 'meeple_state' => 2, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonusSecondMarker_Pass_OpponentBuilding(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        $tileId = 42;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);

        $game->actBonusSecondMarker($tileId,999999);
        
        $newTokenId = 43;
        assertSame(TestDatas::$tokens[$newTokenId], ['result_associative_index' => $newTokenId, 'meeple_id' => $newTokenId, 'meeple_state' => 2, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonusSecondMarker_KO_WrongTileType(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        $tileId = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot second build this tile");
        $game->actBonusSecondMarker($tileId,999999);
    }
    public function test_ActionBonusSecondMarker_KO_SecondBuildingOwned(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        $tileId = 42;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_BUILDING);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot second build this tile");
        $game->actBonusSecondMarker($tileId,999999);
    }
    public function test_ActionBonusSecondMarker_KO_SecondBuildingOpponent(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        $tileId = 41;
        Globals::setCurrentBonus(BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot second build this tile");
        $game->actBonusSecondMarker($tileId,999999);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
}