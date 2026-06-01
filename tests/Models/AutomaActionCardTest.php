<?php

declare(strict_types=1);

namespace Tests\Models;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\AutomaCards;
use ROG\Managers\Players;
use ROG\Models\AutomaActionCard;
use ROG\Models\AutomaActionType;
use ROG\Models\MAIN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;

final class AutomaActionCardTest extends TestCase
{
    // -------------------------------------------------
    // -------------------------------------------------
    
    public function test_play_SailHigher(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardRow = TestDatas::$cards[201];
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardRow['type']]);
        $expectedMovedShip = 25;
        $expectedRiverSpace = 12;//11+1

        $card->play($player);
        
        assertSame($expectedRiverSpace, TestDatas::$tokens[$expectedMovedShip]['meeple_state']);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
        assertSame(0, $player->getScore());
        //Test NO gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
        //TEST players owner rewards :
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(2,  TestDatas::$players[2]['player_score']);//+0
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame(0, TestDatas::$tokens[11]['meeple_state']);
        assertSame(0, TestDatas::$tokens[12]['meeple_state']);
        assertSame(0, TestDatas::$tokens[13]['meeple_state']);
        assertSame(0, TestDatas::$tokens[14]['meeple_state']);
        assertSame(0, TestDatas::$tokens[15]['meeple_state']);
        assertSame(0, TestDatas::$tokens[16]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses']));
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);

    }
    
    public function test_play_SailHigher_AutomaOwner(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardRow = TestDatas::$cards[201];
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardRow['type']]);
        $expectedMovedShip = 25;
        $expectedRiverSpace = 12;//11+1
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[41]['type'] = 34;
        TestDatas::$tiles[41]['tile_state'] = 26;
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['type'] = 42;
        TestDatas::$tiles[43]['tile_state'] = 22;
        TestDatas::$tiles[50] = TestDatas::$tiles[41];
        TestDatas::$tiles[50]['tile_id'] = 50;
        TestDatas::$tiles[50]['result_associative_index'] = 50;
        TestDatas::$tiles[50]['type'] = 43;
        TestDatas::$tiles[50]['tile_state'] = 25;

        $card->play($player);
        
        assertSame($expectedRiverSpace, TestDatas::$tokens[$expectedMovedShip]['meeple_state']);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
        assertSame(2, $player->getScore());//+2
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(2, TestDatas::$tokens[35]['meeple_state']);//+2
        assertSame(4, TestDatas::$tokens[36]['meeple_state']);//+3+1
        //TEST players owner rewards :
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(2,  TestDatas::$players[2]['player_score']);//+0
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame(0, TestDatas::$tokens[11]['meeple_state']);
        assertSame(0, TestDatas::$tokens[12]['meeple_state']);
        assertSame(0, TestDatas::$tokens[13]['meeple_state']);
        assertSame(0, TestDatas::$tokens[14]['meeple_state']);
        assertSame(0, TestDatas::$tokens[15]['meeple_state']);
        assertSame(0, TestDatas::$tokens[16]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses']));
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);

    }
    
    public function test_play_SailHigher_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_5);
        $cardRow = TestDatas::$cards[201];
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardRow['type']]);
        $expectedMovedShip = 25;
        $expectedRiverSpace = 2;//11+5 % 14

        $card->play($player);
        
        assertSame($expectedRiverSpace, TestDatas::$tokens[$expectedMovedShip]['meeple_state']);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
        assertSame(3, $player->getScore());
        //Test discard last building in row :
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[34]['tile_location']);
        //Test NO gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
        //TEST players owner rewards :
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(3,  TestDatas::$players[2]['player_score']);//+1
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame(0, TestDatas::$tokens[11]['meeple_state']);
        assertSame(0, TestDatas::$tokens[12]['meeple_state']);
        assertSame(0, TestDatas::$tokens[13]['meeple_state']);
        assertSame(0, TestDatas::$tokens[14]['meeple_state']);
        assertSame(0, TestDatas::$tokens[15]['meeple_state']);
        assertSame(0, TestDatas::$tokens[16]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses']));
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_MONEY]);//+1
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);
        
    }
    // -------------------------------------------------
    
    public function test_play_SailLower(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardRow = TestDatas::$cards[204];
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardRow['type']]);
        $expectedMovedShip = 26;
        $expectedRiverSpace = 13;//12+1

        $card->play($player);
        
        assertSame($expectedRiverSpace, TestDatas::$tokens[$expectedMovedShip]['meeple_state']);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
        assertSame(0, $player->getScore());
        //Test NO gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
        //TEST players owner rewards :
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(2,  TestDatas::$players[2]['player_score']);//+0
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame(0, TestDatas::$tokens[11]['meeple_state']);
        assertSame(0, TestDatas::$tokens[12]['meeple_state']);
        assertSame(0, TestDatas::$tokens[13]['meeple_state']);
        assertSame(0, TestDatas::$tokens[14]['meeple_state']);
        assertSame(0, TestDatas::$tokens[15]['meeple_state']);
        assertSame(0, TestDatas::$tokens[16]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses']));
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);

    }
    
    public function test_play_SailLower_AutomaOwner(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardRow = TestDatas::$cards[204];
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardRow['type']]);
        $expectedMovedShip = 26;
        $expectedRiverSpace = 13;//12+1
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$tiles[41]['type'] = 34;
        TestDatas::$tiles[41]['tile_state'] = 26;
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['type'] = 42;
        TestDatas::$tiles[43]['tile_state'] = 27;
        TestDatas::$tiles[50] = TestDatas::$tiles[41];
        TestDatas::$tiles[50]['tile_id'] = 50;
        TestDatas::$tiles[50]['result_associative_index'] = 50;
        TestDatas::$tiles[50]['type'] = 43;
        TestDatas::$tiles[50]['tile_state'] = 25;

        $card->play($player);
        
        assertSame($expectedRiverSpace, TestDatas::$tokens[$expectedMovedShip]['meeple_state']);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
        assertSame(2, $player->getScore());//+2
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(6, TestDatas::$tokens[36]['meeple_state']);//+3+1+2
        //TEST players owner rewards :
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(2,  TestDatas::$players[2]['player_score']);//+0
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame(0, TestDatas::$tokens[11]['meeple_state']);
        assertSame(0, TestDatas::$tokens[12]['meeple_state']);
        assertSame(0, TestDatas::$tokens[13]['meeple_state']);
        assertSame(0, TestDatas::$tokens[14]['meeple_state']);
        assertSame(0, TestDatas::$tokens[15]['meeple_state']);
        assertSame(0, TestDatas::$tokens[16]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses']));
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);

    }

    public function test_play_SailLower_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_4);
        $cardRow = TestDatas::$cards[204];
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardRow['type']]);
        $expectedMovedShip = 26;
        $expectedRiverSpace = 2;//12+4 % 14

        $card->play($player);
        
        assertSame($expectedRiverSpace, TestDatas::$tokens[$expectedMovedShip]['meeple_state']);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
        assertSame(3, $player->getScore());
        //Test NO gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
        //TEST players owner rewards :
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        assertSame(3,  TestDatas::$players[2]['player_score']);//+1
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        assertSame(0, TestDatas::$tokens[11]['meeple_state']);
        assertSame(0, TestDatas::$tokens[12]['meeple_state']);
        assertSame(0, TestDatas::$tokens[13]['meeple_state']);
        assertSame(0, TestDatas::$tokens[14]['meeple_state']);
        assertSame(0, TestDatas::$tokens[15]['meeple_state']);
        assertSame(0, TestDatas::$tokens[16]['meeple_state']);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses']));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses']));
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_MONEY]);//+1
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);

    }
    // -------------------------------------------------
    
    public function test_play_Build_Region1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 2;// cost 5
        $expectedTile = 33;

        $card->play($player);
        
        assertSame(REGION_1, $player->getDie());
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(4, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    
    public function test_play_Build_Region2_whenRegion1Full(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 8;//cost 6
        $expectedTile = 33;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 1;
        TestDatas::$tokens[47] = TestDatas::$tokens[41];
        TestDatas::$tokens[47]['result_associative_index'] = 47;
        TestDatas::$tokens[47]['meeple_location'] = MEEPLE_LOCATION_TILE.'47';
        TestDatas::$tiles[47] = TestDatas::$tiles[41];
        TestDatas::$tiles[47]['tile_id'] = 47;
        TestDatas::$tiles[47]['result_associative_index'] = 47;
        TestDatas::$tiles[47]['tile_state'] = 2;
        TestDatas::$tokens[48] = TestDatas::$tokens[41];
        TestDatas::$tokens[48]['result_associative_index'] = 48;
        TestDatas::$tokens[48]['meeple_location'] = MEEPLE_LOCATION_TILE.'48';
        TestDatas::$tiles[48] = TestDatas::$tiles[41];
        TestDatas::$tiles[48]['tile_id'] = 48;
        TestDatas::$tiles[48]['result_associative_index'] = 48;
        TestDatas::$tiles[48]['tile_state'] = 3;

        $card->play($player);
        
        assertSame(REGION_2, $player->getDie());
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(49, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[49], ['result_associative_index' => 49, 'meeple_id' => 49, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(4, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    
    public function test_play_Build_Region6_whenOtherRegionsFull(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 30;
        $expectedTile = 33;
        for($shorespace = 1; $shorespace<30; $shorespace++){
            $tileIndex = 46 + $shorespace;
            TestDatas::$tokens[$tileIndex] = TestDatas::$tokens[41];
            TestDatas::$tokens[$tileIndex]['result_associative_index'] = $tileIndex;
            TestDatas::$tokens[$tileIndex]['meeple_location'] = MEEPLE_LOCATION_TILE."$tileIndex";
            TestDatas::$tiles[$tileIndex] = TestDatas::$tiles[41];
            TestDatas::$tiles[$tileIndex]['tile_id'] = $tileIndex;
            TestDatas::$tiles[$tileIndex]['result_associative_index'] = $tileIndex;
            TestDatas::$tiles[$tileIndex]['tile_state'] = $shorespace;
        }

        $card->play($player);
        
        assertSame(REGION_6, $player->getDie());
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(76, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[76], ['result_associative_index' => 76, 'meeple_id' => 76, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(4, TestDatas::$tokens[36]['meeple_state']);
    }

    public function test_play_Build_Nothing_when0EmptySpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedTile = 33;
        for($shorespace = 1; $shorespace<=30; $shorespace++){
            $tileIndex = 46 + $shorespace;
            TestDatas::$tokens[$tileIndex] = TestDatas::$tokens[41];
            TestDatas::$tokens[$tileIndex]['result_associative_index'] = $tileIndex;
            TestDatas::$tokens[$tileIndex]['meeple_location'] = MEEPLE_LOCATION_TILE."$tileIndex";
            TestDatas::$tiles[$tileIndex] = TestDatas::$tiles[41];
            TestDatas::$tiles[$tileIndex]['tile_id'] = $tileIndex;
            TestDatas::$tiles[$tileIndex]['result_associative_index'] = $tileIndex;
            TestDatas::$tiles[$tileIndex]['tile_state'] = $shorespace;
        }

        $card->play($player);
        
        assertSame(REGION_6, $player->getDie());
        assertSame(null, Globals::getTurnMainActionDone());
         //Test NOT built Tile :
        assertSame(TILE_LOCATION_BUILDING_ROW, TestDatas::$tiles[$expectedTile]['tile_location']);
        //no new clan marker :
        assertSame(1, TestDatas::$lastInsertedId);
        assertFalse( array_key_exists(77,TestDatas::$tokens));
        //Test NOT gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    
    /**
     * Should not happen because in 2p game with automa, after end is triggered, the 2 other players will see minimum 4 then 3 buildings in the row
     */
    public function test_play_Build_Nothing_when0RemainingBuilding(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_1);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        TestDatas::$tiles[31]['tile_location'] = TILE_LOCATION_DISCARD;
        TestDatas::$tiles[32]['tile_location'] = TILE_LOCATION_DISCARD;
        TestDatas::$tiles[33]['tile_location'] = TILE_LOCATION_DISCARD;
        TestDatas::$tiles[34]['tile_location'] = TILE_LOCATION_DISCARD;

        $card->play($player);
        
        assertSame(null, Globals::getTurnMainActionDone());
        //no new clan marker :
        assertSame(1, TestDatas::$lastInsertedId);
        assertFalse( array_key_exists(43,TestDatas::$tokens));
        //Test NOT gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }


    public function test_play_Build_Region2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_2);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 8;//cost 6
        $expectedTile = 33;

        $card->play($player);
        
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(4, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    
    public function test_play_Build_Region3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_3);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 14;//cost 5
        $expectedTile = 33;

        $card->play($player);
        
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(4, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    
    public function test_play_Build_Region4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_4);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 20;//cost 6
        $expectedTile = 33;

        $card->play($player);
        
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(4, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    public function test_play_Build_Region5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_5);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 24;//cost 6
        $expectedTile = 33;

        $card->play($player);
        
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(4, TestDatas::$tokens[35]['meeple_state']);
        assertSame(0, TestDatas::$tokens[36]['meeple_state']);
    }
    
    public function test_play_Build_Region6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        $player->setDie(REGION_6);
        $cardType = AutomaActionType::BUILD->value;
        $cardRow = TestDatas::$cards[207];
        $cardRow['type'] = $cardType;
        $card = new AutomaActionCard($cardRow, AutomaCards::getAutomaActionCardsTypes()[$cardType]);
        $expectedShoreSpace = 30;//cost 5
        $expectedTile = 33;

        $card->play($player);
        
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
         //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$expectedTile]['tile_location']);
        assertSame($expectedShoreSpace, TestDatas::$tiles[$expectedTile]['tile_state']);
        //Test new clan marker
        assertSame(43, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$expectedTile",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[31]['meeple_state']);
        assertSame(0, TestDatas::$tokens[32]['meeple_state']);
        assertSame(0, TestDatas::$tokens[33]['meeple_state']);
        assertSame(0, TestDatas::$tokens[34]['meeple_state']);
        assertSame(0, TestDatas::$tokens[35]['meeple_state']);
        assertSame(4, TestDatas::$tokens[36]['meeple_state']);
    }
    // -------------------------------------------------

}