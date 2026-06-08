<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Cards;
use ROG\Models\ScenarioCard;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class ClanSelectionTest extends TestCase
{

    public function testEnteringState_baseGame(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_OFF);
        unset(TestDatas::$cards[101]);
        unset(TestDatas::$cards[102]);
        unset(TestDatas::$cards[103]);
        unset(TestDatas::$cards[104]);

        $game->stClanSelection();
        
        assertSame(ST_PLAYER_SETUP, GamestateMachine::$test_current_state);
        assertSame(0, Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT));
    }

    
    public function testEnteringState_ClanDraft_NoScenarios(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_DRAFT);
        Globals::setOptionScenarios(OPTION_SCENARIOS_OFF);
        TestDatas::$cards[101] = ['result_associative_index' => 101,'card_id' => 101, 'card_location' => CARD_CLAN_LOCATION_DECK.'1', 'card_state' => 0, 'player_id' => null, 'type' => 1, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[102] = ['result_associative_index' => 102,'card_id' => 102, 'card_location' => CARD_CLAN_LOCATION_DECK.'1', 'card_state' => 0, 'player_id' => null, 'type' => 2, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[103] = ['result_associative_index' => 103,'card_id' => 103, 'card_location' => CARD_CLAN_LOCATION_DECK.'2', 'card_state' => 0, 'player_id' => null, 'type' => 3, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[104] = ['result_associative_index' => 104,'card_id' => 104, 'card_location' => CARD_CLAN_LOCATION_DECK.'2', 'card_state' => 0, 'player_id' => null, 'type' => 4, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[105] = ['result_associative_index' => 105,'card_id' => 105, 'card_location' => CARD_CLAN_LOCATION_DECK.'3', 'card_state' => 0, 'player_id' => null, 'type' => 5, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[106] = ['result_associative_index' => 106,'card_id' => 106, 'card_location' => CARD_CLAN_LOCATION_DECK.'3', 'card_state' => 0, 'player_id' => null, 'type' => 6, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[107] = ['result_associative_index' => 107,'card_id' => 107, 'card_location' => CARD_CLAN_LOCATION_DECK.'4', 'card_state' => 0, 'player_id' => null, 'type' => 7, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[108] = ['result_associative_index' => 108,'card_id' => 108, 'card_location' => CARD_CLAN_LOCATION_DECK.'4', 'card_state' => 0, 'player_id' => null, 'type' => 8, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[111] = ['result_associative_index' => 111,'card_id' => 111, 'card_location' => CARD_CLAN_LOCATION_DECK.'5', 'card_state' => 0, 'player_id' => null, 'type' => 11, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[112] = ['result_associative_index' => 112,'card_id' => 112, 'card_location' => CARD_CLAN_LOCATION_DECK.'5', 'card_state' => 0, 'player_id' => null, 'type' => 12,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[113] = ['result_associative_index' => 113,'card_id' => 113, 'card_location' => CARD_CLAN_LOCATION_DECK.'6', 'card_state' => 0, 'player_id' => null, 'type' => 13,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[114] = ['result_associative_index' => 114,'card_id' => 114, 'card_location' => CARD_CLAN_LOCATION_DECK.'6', 'card_state' => 0, 'player_id' => null, 'type' => 14,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[115] = ['result_associative_index' => 115,'card_id' => 115, 'card_location' => CARD_CLAN_LOCATION_DECK.'7', 'card_state' => 0, 'player_id' => null, 'type' => 15,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[116] = ['result_associative_index' => 116,'card_id' => 116, 'card_location' => CARD_CLAN_LOCATION_DECK.'7', 'card_state' => 0, 'player_id' => null, 'type' => 16,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[117] = ['result_associative_index' => 117,'card_id' => 117, 'card_location' => CARD_CLAN_LOCATION_DECK.'8', 'card_state' => 0, 'player_id' => null, 'type' => 17,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[118] = ['result_associative_index' => 118,'card_id' => 118, 'card_location' => CARD_CLAN_LOCATION_DECK.'8', 'card_state' => 0, 'player_id' => null, 'type' => 18,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        
        $game->stClanSelection();
        
        assertSame(ST_DRAFT_PLAYER, GamestateMachine::$test_current_state);
        //test NO scenarios cards
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DRAFT));
        //test 1 patron / clan :
        assertSame(8, Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT));
    }
    
    public function testEnteringState_ClanDraft_WithScenarios(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_DRAFT);
        Globals::setOptionScenarios(OPTION_SCENARIOS_ON);
        TestDatas::$cards[101] = ['result_associative_index' => 101,'card_id' => 101, 'card_location' => CARD_CLAN_LOCATION_DECK.'1', 'card_state' => 0, 'player_id' => null, 'type' => 1, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[102] = ['result_associative_index' => 102,'card_id' => 102, 'card_location' => CARD_CLAN_LOCATION_DECK.'1', 'card_state' => 0, 'player_id' => null, 'type' => 2, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[103] = ['result_associative_index' => 103,'card_id' => 103, 'card_location' => CARD_CLAN_LOCATION_DECK.'2', 'card_state' => 0, 'player_id' => null, 'type' => 3, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[104] = ['result_associative_index' => 104,'card_id' => 104, 'card_location' => CARD_CLAN_LOCATION_DECK.'2', 'card_state' => 0, 'player_id' => null, 'type' => 4, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[105] = ['result_associative_index' => 105,'card_id' => 105, 'card_location' => CARD_CLAN_LOCATION_DECK.'3', 'card_state' => 0, 'player_id' => null, 'type' => 5, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[106] = ['result_associative_index' => 106,'card_id' => 106, 'card_location' => CARD_CLAN_LOCATION_DECK.'3', 'card_state' => 0, 'player_id' => null, 'type' => 6, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[107] = ['result_associative_index' => 107,'card_id' => 107, 'card_location' => CARD_CLAN_LOCATION_DECK.'4', 'card_state' => 0, 'player_id' => null, 'type' => 7, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[108] = ['result_associative_index' => 108,'card_id' => 108, 'card_location' => CARD_CLAN_LOCATION_DECK.'4', 'card_state' => 0, 'player_id' => null, 'type' => 8, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[111] = ['result_associative_index' => 111,'card_id' => 111, 'card_location' => CARD_CLAN_LOCATION_DECK.'5', 'card_state' => 0, 'player_id' => null, 'type' => 11, 'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[112] = ['result_associative_index' => 112,'card_id' => 112, 'card_location' => CARD_CLAN_LOCATION_DECK.'5', 'card_state' => 0, 'player_id' => null, 'type' => 12,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[113] = ['result_associative_index' => 113,'card_id' => 113, 'card_location' => CARD_CLAN_LOCATION_DECK.'6', 'card_state' => 0, 'player_id' => null, 'type' => 13,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[114] = ['result_associative_index' => 114,'card_id' => 114, 'card_location' => CARD_CLAN_LOCATION_DECK.'6', 'card_state' => 0, 'player_id' => null, 'type' => 14,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[115] = ['result_associative_index' => 115,'card_id' => 115, 'card_location' => CARD_CLAN_LOCATION_DECK.'7', 'card_state' => 0, 'player_id' => null, 'type' => 15,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[116] = ['result_associative_index' => 116,'card_id' => 116, 'card_location' => CARD_CLAN_LOCATION_DECK.'7', 'card_state' => 0, 'player_id' => null, 'type' => 16,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[117] = ['result_associative_index' => 117,'card_id' => 117, 'card_location' => CARD_CLAN_LOCATION_DECK.'8', 'card_state' => 0, 'player_id' => null, 'type' => 17,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[118] = ['result_associative_index' => 118,'card_id' => 118, 'card_location' => CARD_CLAN_LOCATION_DECK.'8', 'card_state' => 0, 'player_id' => null, 'type' => 18,  'subtype' => CARD_TYPE_CLAN_PATRON,];
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'1', 'card_state' => 0, 'player_id' => null, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[302] = ['result_associative_index' => 302,'card_id' => 302, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'2', 'card_state' => 0, 'player_id' => null, 'type' => 2,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[303] = ['result_associative_index' => 303,'card_id' => 303, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'3', 'card_state' => 0, 'player_id' => null, 'type' => 3,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[304] = ['result_associative_index' => 304,'card_id' => 304, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'4', 'card_state' => 0, 'player_id' => null, 'type' => 4,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[305] = ['result_associative_index' => 305,'card_id' => 305, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'5', 'card_state' => 0, 'player_id' => null, 'type' => 5,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[306] = ['result_associative_index' => 306,'card_id' => 306, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'6', 'card_state' => 0, 'player_id' => null, 'type' => 6,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[307] = ['result_associative_index' => 307,'card_id' => 307, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'7', 'card_state' => 0, 'player_id' => null, 'type' => 7,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[308] = ['result_associative_index' => 308,'card_id' => 308, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'8', 'card_state' => 0, 'player_id' => null, 'type' => 8,   'subtype' => CARD_TYPE_SCENARIO,];

        $game->stClanSelection();
        
        assertSame(ST_DRAFT_PLAYER, GamestateMachine::$test_current_state);
        //test scenarios cards in decks to be picked
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[301]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[302]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[303]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[304]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[305]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[306]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[307]['card_location']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[308]['card_location']);
        assertSame(8, Cards::countInLocation(CARD_SCENARIO_LOCATION_DRAFT));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_CRAB    ));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_MANTIS  ));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_CRANE   ));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_SCORPION));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_PHOENIX ));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_LION    ));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_DRAGON  ));
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DECK.CLAN_UNICORN ));
        //Test all patrons available :
        assertSame(16, Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT));
    }
      
    public function testEnteringState_ClanDraftAlternative(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_ALTERNATIVE);
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DECK.'1';
        TestDatas::$cards[101]['player_id'] = null;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DECK.'1';
        TestDatas::$cards[102]['player_id'] = null;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DECK.'2';
        TestDatas::$cards[103]['player_id'] = null;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DECK.'2';
        TestDatas::$cards[104]['player_id'] = null;

        $game->stClanSelection();
        
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
        assertSame(4, Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT));
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[101]['card_location']);
        assertSame(1, TestDatas::$cards[101]['player_id']);
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[102]['card_location']);
        assertSame(1, TestDatas::$cards[102]['player_id']);
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[103]['card_location']);
        assertSame(2, TestDatas::$cards[103]['player_id']);
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[104]['card_location']);
        assertSame(2, TestDatas::$cards[104]['player_id']);
        assertSame(0, Cards::countInLocation(CARD_SCENARIO_LOCATION_DRAFT));
    }
    
    public function testEnteringState_ClanDraftAlternative_Scenarios(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_ALTERNATIVE);
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DECK.'1';
        TestDatas::$cards[101]['player_id'] = null;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DECK.'1';
        TestDatas::$cards[102]['player_id'] = null;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DECK.'2';
        TestDatas::$cards[103]['player_id'] = null;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DECK.'2';
        TestDatas::$cards[104]['player_id'] = null;
        Globals::setOptionScenarios(OPTION_SCENARIOS_ON);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'1', 'card_state' => 0, 'player_id' => null, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[302] = ['result_associative_index' => 302,'card_id' => 302, 'card_location' => CARD_SCENARIO_LOCATION_DECK.'2', 'card_state' => 0, 'player_id' => null, 'type' => 2,   'subtype' => CARD_TYPE_SCENARIO,];

        $game->stClanSelection();
        
        assertSame(4, Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT));
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[101]['card_location']);
        assertSame(1, TestDatas::$cards[101]['player_id']);
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[102]['card_location']);
        assertSame(1, TestDatas::$cards[102]['player_id']);
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[103]['card_location']);
        assertSame(2, TestDatas::$cards[103]['player_id']);
        assertSame(CARD_CLAN_LOCATION_DRAFT, TestDatas::$cards[104]['card_location']);
        assertSame(2, TestDatas::$cards[104]['player_id']);
        assertSame(2, Cards::countInLocation(CARD_SCENARIO_LOCATION_DRAFT));
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[301]['card_location']);
        assertSame(1, TestDatas::$cards[301]['player_id']);
        assertSame(CARD_SCENARIO_LOCATION_DRAFT, TestDatas::$cards[302]['card_location']);
        assertSame(2, TestDatas::$cards[302]['player_id']);
    }
     
}