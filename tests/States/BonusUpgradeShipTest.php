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

final class BonusUpgradeShipTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    public function test_Args_AlreadyUpgraded(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_UPGRADE_SHIP;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL;
        $expectedArgs = [
            'p' => [ ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusUpgrade();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_2Ships(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_UPGRADE_SHIP;
        $expectedArgs = [
            'p' => [ 21, 22, ],//meeples ids
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusUpgrade();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
    
    // -------------------------------------------------
    public function test_ActionBonusUpgrade_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_UPGRADE_SHIP;
        $shipId = 21;

        $game->actBonusUpgrade($shipId);
        
        assertSame(MEEPLE_TYPE_SHIP_ROYAL, TestDatas::$tokens[$shipId]['type']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonusUpgrade_KO_WrongMeepleType(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_UPGRADE_SHIP;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL;
        $shipId = 21;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot upgrade this ship");
        $game->actBonusUpgrade($shipId);
    }
    public function test_ActionBonusUpgrade_KO_WrongMeepleLocation(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_UPGRADE_SHIP;
        $shipId = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot upgrade this ship");
        $game->actBonusUpgrade($shipId);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
}