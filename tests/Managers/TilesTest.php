<?php

declare(strict_types=1);

namespace Tests\Managers;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Managers\Tiles;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class TilesTest extends TestCase
{
    // -------------------------------------------------
    // -------------------------------------------------
    public function test_get2PlayerSideMasteryCardType_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 1, Tiles::get2PlayerSideMasteryCardType(1) );
    }
    public function test_get2PlayerSideMasteryCardType_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 2, Tiles::get2PlayerSideMasteryCardType(2) );
    }
    public function test_get2PlayerSideMasteryCardType_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 3, Tiles::get2PlayerSideMasteryCardType(3) );
    }
    public function test_get2PlayerSideMasteryCardType_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 4, Tiles::get2PlayerSideMasteryCardType(4) );
    }
    public function test_get2PlayerSideMasteryCardType_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 5, Tiles::get2PlayerSideMasteryCardType(5) );
    }
    public function test_get2PlayerSideMasteryCardType_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 6, Tiles::get2PlayerSideMasteryCardType(6) );
    }
    
    public function test_get2PlayerSideMasteryCardType_7(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 1, Tiles::get2PlayerSideMasteryCardType(7) );
    }
    public function test_get2PlayerSideMasteryCardType_8(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 2, Tiles::get2PlayerSideMasteryCardType(8) );
    }
    public function test_get2PlayerSideMasteryCardType_9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 3, Tiles::get2PlayerSideMasteryCardType(9) );
    }
    public function test_get2PlayerSideMasteryCardType_10(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 4, Tiles::get2PlayerSideMasteryCardType(10) );
    }
    public function test_get2PlayerSideMasteryCardType_11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 5, Tiles::get2PlayerSideMasteryCardType(11) );
    }
    public function test_get2PlayerSideMasteryCardType_12(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        
        assertSame( 6, Tiles::get2PlayerSideMasteryCardType(12) );
    }
    // -------------------------------------------------
}