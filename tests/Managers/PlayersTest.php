<?php

declare(strict_types=1);

namespace Tests\Managers;

use Bga\GameFramework\Table;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Managers\Players;

use function PHPUnit\Framework\assertSame;

final class PlayersTest extends TestCase
{
    
    public function test_NextPlayerWithBonusToChoose(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__);
        $player_id = Table::$test_activePlayerId;
        $expected = Table::$test_players[2];
        $game = new GameMock();

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        assertSame($expected, $nextPlayer);
    }
}