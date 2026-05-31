<?php

declare(strict_types=1);

namespace Tests\Managers;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Managers\ShoreSpaces;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class ShoreSpacesTest extends TestCase
{
    // -------------------------------------------------
    // -------------------------------------------------
    public function test_getEmptySpaces_Region1_ALL(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedSpaces = [ 1,2,3,4,5];
        TestDatas::$tiles[41]['tile_location'] = TILE_LOCATION_BUILDING_DECK;
        TestDatas::$tiles[42]['tile_location'] = TILE_LOCATION_BUILDING_DECK;

        $spaces = ShoreSpaces::getEmptySpaces(1);
        
        assertSame( $expectedSpaces, $spaces );
    }
    public function test_getEmptySpaces_Region1_1Built(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedSpaces = [ 1,2,3,  4=>5];//keys [0,1,2,3,4] preserved
        TestDatas::$tiles[42]['tile_location'] = TILE_LOCATION_BUILDING_DECK;

        $spaces = ShoreSpaces::getEmptySpaces(1);
        
        assertSame( $expectedSpaces, $spaces );
    }
    public function test_getEmptySpaces_Region1_2Built(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedSpaces = [ 1,2,3,];

        $spaces = ShoreSpaces::getEmptySpaces(1);
        
        assertSame( $expectedSpaces, $spaces );
    }
    // -------------------------------------------------
    
    public function test_getEmptySpaces_Region2_ALL(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedSpaces = [ 1=>7,2=>8,3=>9,4=>10];

        $spaces = ShoreSpaces::getEmptySpaces(2);
        
        assertSame( $expectedSpaces, $spaces );
    }
    public function test_getEmptySpaces_Region2_1Built(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedSpaces = [ 1=>7, 3=>9, 4=>10];//keys [0,1,2,3,4] preserved
        TestDatas::$tiles[42]['tile_state'] = 8;

        $spaces = ShoreSpaces::getEmptySpaces(2);
        
        assertSame( $expectedSpaces, $spaces );
    }
    public function test_getEmptySpaces_Region2_AllBuilt(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedSpaces = [];
        TestDatas::$tiles[43] = TestDatas::$tiles[42];
        TestDatas::$tiles[44] = TestDatas::$tiles[42];
        TestDatas::$tiles[45] = TestDatas::$tiles[42];
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tiles[45]['result_associative_index'] = 45;
        TestDatas::$tiles[41]['tile_state'] = 6;
        TestDatas::$tiles[42]['tile_state'] = 7;
        TestDatas::$tiles[43]['tile_state'] = 8;
        TestDatas::$tiles[44]['tile_state'] = 9;
        TestDatas::$tiles[45]['tile_state'] = 10;

        $spaces = ShoreSpaces::getEmptySpaces(2);
        
        assertSame( $expectedSpaces, $spaces );
    }
    // -------------------------------------------------
    public function test_getUniqueAdjacentRiverSpaces(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $shoreSpaces = [
            4,6,25
        ];
        $expectedSpaces = [
            1,2,3,4,
            11,12,13,
        ];

        $spaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($shoreSpaces);
        
        assertSame( $expectedSpaces, $spaces );
    }
    public function test_getUniqueAdjacentRiverSpaces_Empty(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $shoreSpaces = [
        ];
        $expectedSpaces = [
        ];

        $spaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($shoreSpaces);
        
        assertSame( $expectedSpaces, $spaces );
    }
    // -------------------------------------------------
}