<?php

declare(strict_types=1);

namespace Tests\Models;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\BuildingTile;
use ROG\Models\RewardEntry;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;

final class RewardEntryTest extends TestCase
{
    // -------------------------------------------------
    
    public function test_rewardPlayer_Points_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_POINTS,2);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame(21, TestDatas::$players[1]['player_score']);
    }
    
    public function test_rewardPlayer_Points_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_POINTS,3);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame(22, TestDatas::$players[1]['player_score']);
    }
    // -------------------------------------------------
    
    public function test_rewardPlayer_Influence_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_INFLUENCE,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame(1, TestDatas::$tokens[$region]['meeple_state']);
    }
    public function test_rewardPlayer_Influence_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_INFLUENCE,3);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame(3, TestDatas::$tokens[$region]['meeple_state']);
    }
    // -------------------------------------------------
    
    public function test_rewardPlayer_Resource_Silk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(RESOURCE_TYPE_SILK,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_rewardPlayer_Resource_Porcelain(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(RESOURCE_TYPE_POTTERY,4);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(4, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_Resource_Rice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(RESOURCE_TYPE_RICE,7);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(6, $resources[RESOURCE_TYPE_RICE]);//max 6
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_Resource_Favor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(RESOURCE_TYPE_SUN,5);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_Resource_Money(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(RESOURCE_TYPE_MONEY,30);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(25, $resources[RESOURCE_TYPE_MONEY]);
    }
    // -------------------------------------------------
    public function test_rewardPlayer_BonusChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_CHOICE,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
    }
    // -------------------------------------------------
    public function test_rewardPlayer_BonusMoneyPerCustomer_NoDeliveries(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_CUSTOMER,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_BonusMoneyPerCustomer_1Deliveries(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_CUSTOMER,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(1, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_BonusMoneyPerCustomer_2Deliveries(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_CUSTOMER,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
    }
    // -------------------------------------------------
    public function test_rewardPlayer_MoneyPerPort(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_PORT,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_MONEY]);
    }
    // -------------------------------------------------
    public function test_rewardPlayer_MoneyPerManor_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_MANOR,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_MoneyPerManor_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_MANOR,1);
        //manor 1
        TestDatas::$tiles[41]['type'] = 14;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        //add a 3rd building tile  + marker
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_MONEY]);
    }
    // -------------------------------------------------
    public function test_rewardPlayer_MoneyPerMarket_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_MARKET,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_MoneyPerMarket_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_MARKET,1);
        //market
        TestDatas::$tiles[41]['type'] = 8;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        //add a 3rd building tile  + marker
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_MONEY]);
    }
    // -------------------------------------------------
    public function test_rewardPlayer_MoneyPerShrine_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_SHRINE,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_rewardPlayer_MoneyPerShrine_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_MONEY_PER_SHRINE,1);
        //manor 1
        TestDatas::$tiles[41]['type'] = 24;

        $reward->rewardPlayer($player, $region,$tile);
        
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    // -------------------------------------------------
    public function test_rewardPlayer_BonusDraw(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_DRAW,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame([BONUS_TYPE_DRAW], json_decode(TestDatas::$players[1]['bonuses'], true));
    }
    // -------------------------------------------------
    public function test_rewardPlayer_BonusTradeKoku(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_TRADE_KOKU,1);
        $expectedBonuses = [
            'datas' => [
                BONUS_TYPE_TRADE_KOKU => [
                    1 => ['koku'=>3,'bonusQuantity'=>1],
                ],
            ],
        ];

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame($expectedBonuses, json_decode(TestDatas::$players[1]['bonuses'], true));
    }
    // -------------------------------------------------
    public function test_rewardPlayer_BonusTradePoints(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_TRADE_POINTS,1);
        $expectedBonuses = [
            'datas' => [
                BONUS_TYPE_TRADE_POINTS => [
                    1 => ['points'=>2,'bonusQuantity'=>1],
                ],
            ],
        ];

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame($expectedBonuses, json_decode(TestDatas::$players[1]['bonuses'], true));
    }
    public function test_rewardPlayer_Automa_BonusTradePoints(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(AUTOMA_PLAYER_ID);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(BONUS_TYPE_TRADE_POINTS,1);
        $expectedBonuses = [
        ];

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame(2, $player->getScore());
        assertSame(2, TestDatas::$stats['table'][14]);
    }
    // -------------------------------------------------
    
    public function test_rewardPlayer_NotSupported(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $tile = new BuildingTile( TestDatas::$tiles[21], Tiles::getBuildingTilesTypes()[1]);
        $reward = new RewardEntry(999,1);

        $reward->rewardPlayer($player, $region,$tile);
        
        assertSame(1,1);
    }
    // -------------------------------------------------

}