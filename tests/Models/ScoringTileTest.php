<?php

declare(strict_types=1);

namespace Tests\Models;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Tiles;
use ROG\Models\ScoringTile;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;

final class ScoringTileTest extends TestCase
{


    // -------------------------------------------------
    
    public function test_computeScore_Type1_WorstPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 1;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [0,1,11];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type1_MidPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 1;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [0,1,11];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type1_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 1;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 12;
        $opponentPositions = [0,1,11];
        $expectedScoring = 3;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type1_BestPosition_Tie(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 1;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 11;
        $opponentPositions = [11];
        $expectedScoring = 1.0;//share 3+0

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    // -------------------------------------------------
    
    // -------------------------------------------------
    
    public function test_computeScore_Type2_LastFarPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 2;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 5;
        $opponentPositions = [11];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type2_LastNearPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 2;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [11];
        $expectedScoring = 2;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type2_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 2;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 12;
        $opponentPositions = [11];
        $expectedScoring = 5;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    
    // -------------------------------------------------
    
    public function test_computeScore_Type3_LastFarPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 3;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [7];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type3_LastNearPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 3;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [7];
        $expectedScoring = 2;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type3_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 3;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [7];
        $expectedScoring = 4;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    // -------------------------------------------------
    public function test_computeScore_Type4_LastFarPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 4;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [7];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type4_LastNearPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 4;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [7];
        $expectedScoring = 4;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type4_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 4;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [7];
        $expectedScoring = 8;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    // -------------------------------------------------
    public function test_computeScore_Type5_LastFarPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 5;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [7];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type5_LastNearPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 5;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [7];
        $expectedScoring = 3;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type5_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 5;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [7];
        $expectedScoring = 6;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    // -------------------------------------------------
    public function test_computeScore_Type6_LastFarPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 6;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [7];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type6_LastNearPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 6;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [7];
        $expectedScoring = 3;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type6_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 6;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [7];
        $expectedScoring = 7;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type6_BestPosition_Tie(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 6;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 10;
        $opponentPositions = [$playerPosition];
        $expectedScoring = 5.0;//share 7+3

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    // -------------------------------------------------
    
    public function test_computeScore_Type7_LastPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 7;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [0,1,11];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type7_MidPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 7;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [0,1,7];
        $expectedScoring = 3;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type7_BestPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 7;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 11;
        $opponentPositions = [0,1,7];
        $expectedScoring = 7;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type7_BestPosition_Tie3p(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 7;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 11;
        $opponentPositions = [11,11];
        $expectedScoring = 3.0;//share 7+3 with 3 players

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    
    // -------------------------------------------------
    public function test_computeScore_Type10_LastPosition(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 0;
        $opponentPositions = [1,2,11];
        $expectedScoring = 0;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type10_Position3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 2;
        $opponentPositions = [0,3,7];
        $expectedScoring = 4;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type10_Position3_Tie1p(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 2;
        $opponentPositions = [2,3,7];
        $expectedScoring = 2.0;//share 4 with 1player

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type10_Position2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [0,1,7];
        $expectedScoring = 8;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    
    public function test_computeScore_Type10_Position2_Tie2p(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 6;
        $opponentPositions = [6,6,7];
        $expectedScoring = 4.0;//share 8 +4 with 2p

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type10_Position1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 11;
        $opponentPositions = [0,1,7];
        $expectedScoring = 12;

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    public function test_computeScore_Type10_Position1_Tie3p(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $type = 10;
        $tile = new ScoringTile( TestDatas::$tiles[11], Tiles::getScoringTilesTypes()[$type]);
        $playerPosition = 11;
        $opponentPositions = [11,11];
        $expectedScoring = 8.0;//share 12+8+4 with 3 players

        $score = $tile->computeScore($playerPosition,$opponentPositions);
        
        assertSame($expectedScoring, $score);
    }
    // -------------------------------------------------
    // -------------------------------------------------
}