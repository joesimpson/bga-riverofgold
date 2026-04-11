<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use Tests\Utils\PHPUnitUtil;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class SetupTest extends TestCase
{
    // ----------------------------------------------------------------------
    public function test_setupNewGame(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
    }

    // ----------------------------------------------------------------------
    public function testEnteringState_PlayerSetup(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
    }
    // ----------------------------------------------------------------------
    
    
}