<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

final class ConfirmChoicesTest extends TestCase
{
    // -------------------------------------------------
 
    public function testEnteringState(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_CHOICES;

        $game->stConfirmChoices();
        
        //Test go to next state
        assertSame(ST_CONFIRM_TURN, GamestateMachine::$test_current_state);
    }
}