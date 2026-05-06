<?php

declare(strict_types=1);

namespace Tests\Managers;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Helpers\Utils;
use ROG\Managers\Players;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class UtilsTest extends TestCase
{
    // -------------------------------------------------
    // -------------------------------------------------
    public function test_playTradersAbilities_KO_NoTraders(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 1;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
    }
    
    public function test_playTradersAbilities_Pass_Trader1_Die1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[13]['type'] = CARD_TRADER_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(20, TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
    }
    
    public function test_playTradersAbilities_Pass_Trader5_Die5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 5;
        TestDatas::$cards[13]['type'] = CARD_TRADER_5;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(20, TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
    }
    
    //Test we don't activate 2 traders bonuses, because each trader is in a different region
    public function test_playTradersAbilities_Pass_2Traders_Die4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$cards[11]['type'] = CARD_TRADER_1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_TRADER_4;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(20, TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
    }
    
    public function test_playTradersAbilities_Pass_2Customers_Die3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 3;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_3;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_TRADER_3;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(20, TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
    }
    
    public function test_playTradersAbilities_KO_Trader1_Die2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$cards[13]['type'] = CARD_TRADER_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
    }
    
    public function test_playTradersAbilities_KO_Trader2_Die1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[13]['type'] = CARD_TRADER_2;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);

        Utils::playTradersAbilities($player);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
    }
    // -------------------------------------------------
}