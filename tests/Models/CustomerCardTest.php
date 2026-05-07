<?php

declare(strict_types=1);

namespace Tests\Models;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\BuildingTile;
use ROG\Models\CustomerCard;
use ROG\Models\RewardEntry;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;

final class CustomerCardTest extends TestCase
{
    // -------------------------------------------------
    // -------------------------------------------------
    
    public function test_playDeliveryAbility_Artisan_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ARTISAN_1;
        $region = 1;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Artisan_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ARTISAN_2;
        $region = 2;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(2, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Artisan_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ARTISAN_3;
        $region = 3;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(2, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Artisan_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ARTISAN_4;
        $region = 4;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(2, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Artisan_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ARTISAN_5;
        $region = 5;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(2, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Artisan_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ARTISAN_6;
        $region = 6;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(2, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    // -------------------------------------------------
    public function test_playDeliveryAbility_Elder_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ELDER_1;
        $region = 1;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Elder_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ELDER_2;
        $region = 2;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Elder_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ELDER_3;
        $region = 3;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Elder_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ELDER_4;
        $region = 4;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Elder_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ELDER_5;
        $region = 5;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Elder_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_ELDER_6;
        $region = 6;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."$region",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    // -------------------------------------------------
    
    public function test_playDeliveryAbility_Merchant_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MERCHANT_1;
        $region = 1;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(3, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_MERCHANT,'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Merchant_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MERCHANT_2;
        $region = 2;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(3, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_MERCHANT,'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Merchant_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MERCHANT_3;
        $region = 3;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(3, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_MERCHANT,'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Merchant_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MERCHANT_4;
        $region = 4;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_MERCHANT,'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Merchant_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MERCHANT_5;
        $region = 5;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_MERCHANT,'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    public function test_playDeliveryAbility_Merchant_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MERCHANT_6;
        $region = 6;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_MERCHANT,'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    // -------------------------------------------------
    /**
     * Should be the same for monk 3 and 5
     */
    public function test_playDeliveryAbility_Monk_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MONK_1;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $resources = json_decode(TestDatas::$players[1]['resources'],true);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//3+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//0+2
        assertSame([BONUS_TYPE_SECOND_MARKER_ON_BUILDING], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Monk_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MONK_3;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $resources = json_decode(TestDatas::$players[1]['resources'],true);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//3+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//0+2
        assertSame([BONUS_TYPE_SECOND_MARKER_ON_BUILDING], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Monk_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MONK_5;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $resources = json_decode(TestDatas::$players[1]['resources'],true);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//3+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//0+2
        assertSame([BONUS_TYPE_SECOND_MARKER_ON_BUILDING], json_decode(TestDatas::$players[1]['bonuses']));
    }
     /**
     * Should be the same for monk 4 and 6
     */
    public function test_playDeliveryAbility_Monk_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MONK_2;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $resources = json_decode(TestDatas::$players[1]['resources'],true);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//3+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//0+2
        assertSame([BONUS_TYPE_SECOND_MARKER_ON_OPPONENT], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Monk_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MONK_4;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $resources = json_decode(TestDatas::$players[1]['resources'],true);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//3+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//0+2
        assertSame([BONUS_TYPE_SECOND_MARKER_ON_OPPONENT], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Monk_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_MONK_6;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        $resources = json_decode(TestDatas::$players[1]['resources'],true);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//3+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//0+2
        assertSame([BONUS_TYPE_SECOND_MARKER_ON_OPPONENT], json_decode(TestDatas::$players[1]['bonuses']));
    }
    // -------------------------------------------------
    
    public function test_playDeliveryAbility_Noble_1_NoRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_1;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Noble_1_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_1;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $card->playDeliveryAbility($player);
        
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Noble_2_NoRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_2;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(2, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Noble_3_NoRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_3;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(2, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Noble_4_NoRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_4;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(2, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Noble_5_NoRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_5;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(2, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Noble_6_NoRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = CARD_NOBLE_6;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(2, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Magistrate_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 31;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(6, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Magistrate_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 32;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(6, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Magistrate_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 33;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(6, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Magistrate_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 34;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(6, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Magistrate_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 35;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(6, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Magistrate_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 36;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(6, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Spy(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 50;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame([BONUS_TYPE_ADVANCE_OR_POINTS], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Smuggler_1_WithBuildings(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 37;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_ANY_OWNER_REWARD], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Smuggler_1_NoBuildings(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 37;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);
        unset(TestDatas::$tokens[41]);

        $card->playDeliveryAbility($player);
        
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Smuggler_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 38;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(2, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_ANY_OWNER_REWARD], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Smuggler_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 39;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(2, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_ANY_OWNER_REWARD], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Smuggler_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 40;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(2, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_ANY_OWNER_REWARD], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Smuggler_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 41;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(2, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_ANY_OWNER_REWARD], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Smuggler_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 42;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(2, TestDatas::$tokens[6]['meeple_state']);
        assertSame([BONUS_TYPE_ANY_OWNER_REWARD], json_decode(TestDatas::$players[1]['bonuses']));
    }
    
    public function test_playDeliveryAbility_Shin_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 43;
        $cardId = 11;
        $cardRow = TestDatas::$cards[$cardId];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."$cardId",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }
    
    public function test_playDeliveryAbility_Shin_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 48;
        $cardId = 11;
        $cardRow = TestDatas::$cards[$cardId];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(2, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."$cardId",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }

    public function test_playDeliveryAbility_Trader_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 55;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Trader_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 56;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(1, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Trader_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 57;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Trader_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 58;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(1, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Trader_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 59;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(1, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    public function test_playDeliveryAbility_Trader_6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $cardType = 60;
        $cardRow = TestDatas::$cards[11];
        $cardRow['type'] = $cardType;
        $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardType]);

        $card->playDeliveryAbility($player);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(1, TestDatas::$tokens[6]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
    }
    // -------------------------------------------------

}