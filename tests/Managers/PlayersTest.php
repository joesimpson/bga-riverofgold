<?php

declare(strict_types=1);

namespace Tests\Managers;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Managers\Players;
use ROG\Models\Player;
use Tests\Utils\TestDatas;

final class PlayersTest extends TestCase
{
    public function test_NextPlayerWithBonusToChoose_SamePlayer(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 1;
        TestDatas::$players[2]['bonuses'] = '['.BONUS_TYPE_CHOICE.']';
        TestDatas::$players[1]['bonuses'] = '['.BONUS_TYPE_DRAW.']';
        $game = new GameMock();

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }

    
    public function test_NextPlayerWithBonusToChoose_1(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 1;
        TestDatas::$players[2]['bonuses'] = '[]';
        TestDatas::$players[1]['bonuses'] = '['.BONUS_TYPE_DRAW.']';
        $game = new GameMock();

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }

    public function test_NextPlayerWithBonusToChoose_2(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 2;
        TestDatas::$players[1]['bonuses'] = '[]';
        TestDatas::$players[2]['bonuses'] = '['.BONUS_TYPE_DRAW.']';
        $game = new GameMock();

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }
    
    
    public function test_NextPlayerWithBonusToChoose_null(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        TestDatas::$players[1]['bonuses'] = '[]';
        TestDatas::$players[2]['bonuses'] = '[]';
        $game = new GameMock();

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertNull($nextPlayer);
    }
}