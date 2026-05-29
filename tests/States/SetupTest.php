<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Tiles;
use ROG\Models\CustomerCard;
use Tests\Utils\PHPUnitUtil;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotSame;

final class SetupTest extends TestCase
{
    // ----------------------------------------------------------------------
    public function test_setupNewGame_NoExpansions(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_OFF,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        TestDatas::$cards = [];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        
        $player1Resources = json_decode(TestDatas::$players[3]['resources'], true);
        assertSame(7, $player1Resources [RESOURCE_TYPE_MONEY]);
        $player2Resources = json_decode(TestDatas::$players[4]['resources'], true);
        assertSame(8, $player2Resources [RESOURCE_TYPE_MONEY]);
        assertSame(null, Globals::getRegionCustomTracks());
        assertSame(OPTION_SEISHIN_OFF, Globals::getOptionSeishin());
        assertSame(0, Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK));
    }
    public function test_setupNewGame_DraftAlternative(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_ALTERNATIVE,
            OPTION_TRACKS => OPTION_TRACKS_OFF,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
    }

    public function test_setupNewGame_Draft_5players(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        TestDatas::$players[5] = TestDatas::$players[1];
        TestDatas::$players[5]['player_id'] = 5;
        TestDatas::$players[5]['result_associative_index'] = 5;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
            3 => TestDatas::$players[3],
            4 => TestDatas::$players[4],
            5 => TestDatas::$players[5],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_DRAFT,
            OPTION_TRACKS => OPTION_TRACKS_OFF,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        
        //Cannot access protected method GameMock::setupNewGame() from SetupTest scope.
        //$game->setupNewGame($playersDatas, $options);
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
    }
    
    public function test_setupNewGame_Customers_Base(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_OFF,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        TestDatas::$cards = [];
        $expectedCustomerTypes = [1,2,3,4,5];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        //We need 6*5 cards
        assertSame(30, count(TestDatas::$cards));
        foreach(TestDatas::$cards as $cardRow){
            $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardRow['type']]);
            $cType = $card->getCustomerType();
            assertTrue(in_array($card->getCustomerType(),$expectedCustomerTypes), "Customer type $cType must be in ".json_encode($expectedCustomerTypes));
        }
    }
    
    public function test_setupNewGame_Customers_IntoCity(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_OFF,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_INTOCITY,
        ];
        TestDatas::$cards = [];
        $expectedCustomerTypes = [CUSTOMER_TYPE_ELDER,CUSTOMER_TYPE_MERCHANT,CUSTOMER_TYPE_MONK,CUSTOMER_TYPE_NOBLE,CUSTOMER_TYPE_MAGISTRATE,CUSTOMER_TYPE_SPY];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        //We need 6*6 cards
        assertSame(36, count(TestDatas::$cards));
        foreach(TestDatas::$cards as $cardRow){
            $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardRow['type']]);
            $cType = $card->getCustomerType();
            assertTrue(in_array($card->getCustomerType(),$expectedCustomerTypes), "Customer type $cType must be in ".json_encode($expectedCustomerTypes));
            assertSame(CARD_LOCATION_DECK,$card->getLocation());
        }
        
    }
    
    public function test_setupNewGame_Customers_Random(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_OFF,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_INTOCITY,
        ];
        TestDatas::$cards = [];
        $expectedCustomerTypes = ALL_CUSTOMER_TYPES;
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        //We need 6*6 cards
        assertSame(36, count(TestDatas::$cards));
        foreach(TestDatas::$cards as $cardRow){
            $card = new CustomerCard($cardRow, Cards::getCustomerCardsTypes()[$cardRow['type']]);
            $cType = $card->getCustomerType();
            assertTrue(in_array($card->getCustomerType(),$expectedCustomerTypes), "Customer type $cType must be in ".json_encode($expectedCustomerTypes));
            assertSame(CARD_LOCATION_DECK,$card->getLocation());
        }
        
    }

    public function test_setupNewGame_Tracks_Random(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_CUSTOM,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
        ];
        TestDatas::$cards = [];
        $expectedTracks = [1,2,3,4,5,6];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        $customTracks = Globals::getRegionCustomTracks();
        assertSame(6, count($customTracks));
        foreach (REGIONS as $region){
            $track = $customTracks[$region];
            assertTrue(in_array($track,$expectedTracks), "Region track $region : $track must be in ".json_encode($expectedTracks));
        }
        
    }
    
    public function test_setupNewGame_Markets_Base(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
            3 => TestDatas::$players[3],
            4 => TestDatas::$players[4],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_CUSTOM,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
            OPTION_MARKETS => OPTION_MARKETS_BASE,
        ];
        TestDatas::$tiles = [];
        $expectedMarketTypes = [44,45,46];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        $tiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_SHORE);
        assertSame(3, count($tiles));// 3+ 0 with 4+ players
        foreach ($tiles as $tile){
            $type = $tile->getType();
            assertTrue(in_array($type,$expectedMarketTypes), "Imperial market type $type must be in ".json_encode($expectedMarketTypes));
        }
        
    }
    public function test_setupNewGame_Markets_Night(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
            3 => TestDatas::$players[3],
            4 => TestDatas::$players[4],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_CUSTOM,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
            OPTION_MARKETS => OPTION_MARKETS_NIGHT,
        ];
        TestDatas::$tiles = [];
        $expectedMarketTypes = [47,48,49];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        $tiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_SHORE);
        assertSame(3, count($tiles));// 3+ 0 with 4+ players
        foreach ($tiles as $tile){
            $type = $tile->getType();
            assertTrue(in_array($type,$expectedMarketTypes), "Imperial market type $type must be in ".json_encode($expectedMarketTypes));
        }
    }
    
    public function test_setupNewGame_Markets_Random(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
            3 => TestDatas::$players[3],
            4 => TestDatas::$players[4],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_TRACKS => OPTION_TRACKS_CUSTOM,
            OPTION_CUSTOMERS => OPTION_CUSTOMERS_BASE,
            OPTION_MARKETS => OPTION_MARKETS_RANDOM,
        ];
        TestDatas::$tokens = [];
        TestDatas::$cards = [];
        TestDatas::$tiles = [];
        $expectedMarketTypes = [44,45,46,47,48,49];
        
        PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        $tiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_SHORE);
        assertSame(3, count($tiles));// 3+ 0 with 4+ players
        foreach ($tiles as $tile){
            $type = $tile->getType();
            assertTrue(in_array($type,$expectedMarketTypes), "Imperial market type $type must be in ".json_encode($expectedMarketTypes));
        }
    }

    public function test_setupNewGame_Seishin_Easy(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_SEISHIN => OPTION_SEISHIN_LEVEL_1,
        ];
        $expectedCardsNames = [
            'Sail the Higher Ship',
            'Sail the Higher Ship',
            'Sail the Higher Ship',
            'Sail the Lower Ship',
            'Sail the Lower Ship',
            'Deliver to a Customer',
            'Build a Building',
            'Build a Building',
            'Advance in the City of Lies',
        ];
        TestDatas::$cards = [];
        TestDatas::$tiles = [];
        
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        $player1Resources = json_decode(TestDatas::$players[3]['resources'], true);
        assertSame(7, $player1Resources [RESOURCE_TYPE_MONEY]);
        $player2Resources = json_decode(TestDatas::$players[4]['resources'], true);
        assertSame(8, $player2Resources [RESOURCE_TYPE_MONEY]);
        assertSame(OPTION_SEISHIN_LEVEL_1, Globals::getOptionSeishin());
        $clan = Globals::getAutomaClan();
        assertTrue( in_array( $clan, CLANS_COLORS), "Clan $clan must be defined in list");
        assertSame(true, Utils::isGameWithAutoma());
        //Check automa deck :
        assertSame(9, Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK));
        $automaCards = Cards::getInLocation(CARD_AUTOMA_LOCATION_DECK);
        $cardsNames = $automaCards->map(function($card) {return $card->getTitle();})->toArray();
        assertSame($expectedCardsNames, $cardsNames);
        //check masteries on 3p side :
        $masteries = Tiles::getInLocation(TILE_LOCATION_MASTERY_CARD);
        foreach($masteries as $mastery){
            $pSide = Tiles::get2PlayerSideMasteryCardType($mastery->getType());
            assertNotSame( $pSide, $mastery->getType());
        }
        //check scoring tiles on 3p side :
        $scoringTiles = Tiles::getInLocation(TILE_LOCATION_SCORING);
        foreach($scoringTiles as $scoringTile){
            $tilePlayers = $scoringTile->getNbPlayers();
            assertTrue( in_array(3, $tilePlayers), "scoring Tile must be for 3 players : ".json_encode($tilePlayers));
        }
    }
    
    public function test_setupNewGame_Seishin_Solo(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_SEISHIN => OPTION_SEISHIN_LEVEL_1,
        ];
        $expectedCardsNames = [
            'Sail the Higher Ship',
            'Sail the Higher Ship',
            'Sail the Higher Ship',
            'Sail the Lower Ship',
            'Sail the Lower Ship',
            'Deliver to a Customer',
            'Build a Building',
            'Build a Building',
            'Advance in the City of Lies',
        ];
        TestDatas::$cards = [];
        TestDatas::$tiles = [];
        
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        assertSame(OPTION_SEISHIN_LEVEL_1, Globals::getOptionSeishin());
        $clan = Globals::getAutomaClan();
        assertTrue( in_array( $clan, CLANS_COLORS), "Clan $clan must be defined in list");
        assertSame(true, Utils::isGameWithAutoma());
        //Check automa deck :
        assertSame(9, Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK));
        $automaCards = Cards::getInLocation(CARD_AUTOMA_LOCATION_DECK);
        $cardsNames = $automaCards->map(function($card) {return $card->getTitle();})->toArray();
        assertSame($expectedCardsNames, $cardsNames);
        //check masteries on 2p side :
        $masteries = Tiles::getInLocation(TILE_LOCATION_MASTERY_CARD);
        foreach($masteries as $mastery){
            $pSide = Tiles::get2PlayerSideMasteryCardType($mastery->getType());
            assertSame( $pSide, $mastery->getType());
        }
        //check scoring tiles on 2p side :
        $scoringTiles = Tiles::getInLocation(TILE_LOCATION_SCORING);
        foreach($scoringTiles as $scoringTile){
            $tilePlayers = $scoringTile->getNbPlayers();
            assertTrue( in_array(2, $tilePlayers), "scoring Tile must be for 2 players : ".json_encode($tilePlayers));
        }
    }
    
    public function test_setupNewGame_Seishin_Normal(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_SEISHIN => OPTION_SEISHIN_LEVEL_2,
        ];
        $expectedCardsNames = [
            'Sail the Higher Ship',
            'Sail the Higher Ship',
            'Sail the Higher Ship',
            'Sail the Lower Ship',
            'Sail the Lower Ship',
            'Deliver to a Customer',
            'Deliver to a Customer',
            'Build a Building',
            'Build a Building',
            'Advance in the City of Lies',
        ];
        TestDatas::$cards = [];
        
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        $player1Resources = json_decode(TestDatas::$players[3]['resources'], true);
        assertSame(7, $player1Resources [RESOURCE_TYPE_MONEY]);
        $player2Resources = json_decode(TestDatas::$players[4]['resources'], true);
        assertSame(8, $player2Resources [RESOURCE_TYPE_MONEY]);
        assertSame(OPTION_SEISHIN_LEVEL_2, Globals::getOptionSeishin());
        $clan = Globals::getAutomaClan();
        assertTrue( in_array( $clan, CLANS_COLORS), "Clan $clan must be defined in list");
        assertSame(true, Utils::isGameWithAutoma());
        //Check automa deck :
        assertSame(10, Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK));
        $automaCards = Cards::getInLocation(CARD_AUTOMA_LOCATION_DECK);
        $cardsNames = $automaCards->map(function($card) {return $card->getTitle();})->toArray();
        assertSame($expectedCardsNames, $cardsNames);
    }
    
    public function test_setupNewGame_Seishin_Master(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
            OPTION_SEISHIN => OPTION_SEISHIN_LEVEL_5,
        ];
        $expectedCardsNames = [
            'Sail the Lower Ship',
            'Sail the Lower Ship',
            'Deliver to a Customer',
            'Deliver to a Customer',
            'Build a Building',
            'Build a Building',
            'Advance in the City of Lies',
        ];
        TestDatas::$cards = [];
        
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        $player1Resources = json_decode(TestDatas::$players[3]['resources'], true);
        assertSame(7, $player1Resources [RESOURCE_TYPE_MONEY]);
        $player2Resources = json_decode(TestDatas::$players[4]['resources'], true);
        assertSame(8, $player2Resources [RESOURCE_TYPE_MONEY]);
        assertSame(OPTION_SEISHIN_LEVEL_5, Globals::getOptionSeishin());
        $clan = Globals::getAutomaClan();
        assertTrue( in_array( $clan, CLANS_COLORS), "Clan $clan must be defined in list");
        assertSame(true, Utils::isGameWithAutoma());
        //Check automa deck :
        assertSame(7, Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK));
        $automaCards = Cards::getInLocation(CARD_AUTOMA_LOCATION_DECK);
        $cardsNames = $automaCards->map(function($card) {return $card->getTitle();})->toArray();
        assertSame($expectedCardsNames, $cardsNames);
    }
    
    public function test_setupNewGame_Seishin_Master_WithClanSelection(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_GAME_SETUP;
        $playersDatas = [
            1 => TestDatas::$players[1],
            2 => TestDatas::$players[2],
        ] ;
        $options = [
            OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_DRAFT,
            OPTION_SEISHIN => OPTION_SEISHIN_LEVEL_5,
        ];
        $expectedCardsNames = [
            'Sail the Lower Ship',
            'Sail the Lower Ship',
            'Deliver to a Customer',
            'Deliver to a Customer',
            'Build a Building',
            'Build a Building',
            'Advance in the City of Lies',
        ];
        TestDatas::$cards = [];
        
        $returnState = PHPUnitUtil::callMethod($game,'setupNewGame', [$playersDatas, $options ]);

        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
        assertSame(ST_CLAN_SELECTION, $returnState);
        $player1Resources = json_decode(TestDatas::$players[3]['resources'], true);
        assertSame(7, $player1Resources [RESOURCE_TYPE_MONEY]);
        $player2Resources = json_decode(TestDatas::$players[4]['resources'], true);
        assertSame(8, $player2Resources [RESOURCE_TYPE_MONEY]);
        assertSame(OPTION_SEISHIN_LEVEL_5, Globals::getOptionSeishin());
        $clan = Globals::getAutomaClan();
        assertSame( 0, $clan, "Clan $clan must be UNdefined");
        assertSame(true, Utils::isGameWithAutoma());
        //Check automa deck :
        assertSame(7, Cards::countInLocation(CARD_AUTOMA_LOCATION_DECK));
        $automaCards = Cards::getInLocation(CARD_AUTOMA_LOCATION_DECK);
        $cardsNames = $automaCards->map(function($card) {return $card->getTitle();})->toArray();
        assertSame($expectedCardsNames, $cardsNames);
    }
    // ----------------------------------------------------------------------
    public function testEnteringState_PlayerSetup(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //Clear hand :
        unset(TestDatas::$cards[11]);
        unset(TestDatas::$cards[12]);
        unset(TestDatas::$cards[13]);

        $game->stPlayerSetup();

        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(10, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(0, TestDatas::$players[1]['skip_roll_die']);
        assertSame(2, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }

    public function testEnteringState_PlayerSetup_PatronDarling(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //Clear hand :
        unset(TestDatas::$cards[11]);
        unset(TestDatas::$cards[12]);
        unset(TestDatas::$cards[13]);

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        
        //false because of rollDie()
        assertSame(0, TestDatas::$players[1]['skip_roll_die']);
        assertSame(2, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }
    public function testEnteringState_PlayerSetup_PatronSonOfStorm(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_SON_OF_STORM;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        //Clear hand :
        unset(TestDatas::$cards[11]);
        unset(TestDatas::$cards[12]);
        unset(TestDatas::$cards[13]);

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(0, TestDatas::$players[1]['skip_roll_die']);
        assertSame(3, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }
    
    public function testEnteringState_PlayerSetup_PatronLadyOfLions(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$players[1]['skip_roll_die'] = 0;
        TestDatas::$cards[101]['type'] = PATRON_LIONS_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->stPlayerSetup();
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(json_encode([BONUS_TYPE_PLACE_LION]), TestDatas::$players[1]['bonuses']);
        //assertSame(1, TestDatas::$players[1]['skip_roll_die']);
    }
    
    public function testEnteringState_PlayerSetup_PatronMagnateSandRoad(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        TestDatas::$cards[101]['type'] = PATRON_MAGNATE_SAND_ROAD ;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->stPlayerSetup();
        
        assertSame(ST_NEXT_TURN, GamestateMachine::$test_current_state);
        //Test 3 ships
        assertSame(1,TestDatas::$tokens[49]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[49]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP,TestDatas::$tokens[49]['type']);
        assertSame(1,TestDatas::$tokens[50]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[50]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP,TestDatas::$tokens[50]['type']);
        assertSame(1,TestDatas::$tokens[51]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[51]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP_ROYAL,TestDatas::$tokens[51]['type']);
        assertSame(1,TestDatas::$tokens[51]['meeple_state']);
        
    }
    
    public function testEnteringState_PlayerSetup_Seishin(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_SETUP;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);

        $game->stPlayerSetup();

        //test influence 0
        assertSame(0, TestDatas::$tokens[59]['meeple_state']);
        assertSame(MEEPLE_LOCATION_INFLUENCE.REGION_1, TestDatas::$tokens[59]['meeple_location']);
        assertSame(0, TestDatas::$tokens[60]['meeple_state']);
        assertSame(MEEPLE_LOCATION_INFLUENCE.REGION_2, TestDatas::$tokens[60]['meeple_location']);
        assertSame(0, TestDatas::$tokens[61]['meeple_state']);
        assertSame(MEEPLE_LOCATION_INFLUENCE.REGION_3, TestDatas::$tokens[61]['meeple_location']);
        assertSame(0, TestDatas::$tokens[62]['meeple_state']);
        assertSame(MEEPLE_LOCATION_INFLUENCE.REGION_4, TestDatas::$tokens[62]['meeple_location']);
        assertSame(0, TestDatas::$tokens[63]['meeple_state']);
        assertSame(MEEPLE_LOCATION_INFLUENCE.REGION_5, TestDatas::$tokens[63]['meeple_location']);
        assertSame(0, TestDatas::$tokens[64]['meeple_state']);
        assertSame(MEEPLE_LOCATION_INFLUENCE.REGION_6, TestDatas::$tokens[64]['meeple_location']);
        //Test 2 ships
        assertSame(AUTOMA_PLAYER_ID,TestDatas::$tokens[65]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[65]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP,TestDatas::$tokens[65]['type']);
        assertSame(AUTOMA_PLAYER_ID,TestDatas::$tokens[66]['player_id']);
        assertSame(MEEPLE_LOCATION_RIVER,TestDatas::$tokens[66]['meeple_location']);
        assertSame(MEEPLE_TYPE_SHIP,TestDatas::$tokens[66]['type']);
    }
    // ----------------------------------------------------------------------
    
    
}