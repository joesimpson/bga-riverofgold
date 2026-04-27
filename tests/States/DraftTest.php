<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use feException;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class DraftTest extends TestCase
{

    // ----------------------------------------------------------------------

    public function testArgs_Draft(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        $args = $game->argDraft();
        $expectedArgs = [
            'cards' => [ 
                        [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                    [
                        'id' => 103,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_LADY,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Lady of Whispers',
                        'name' => 'Bayushi Kashiko',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                    ],
                    [
                        'id' => 104,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_GOVERNOR,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Governor of the City of lies',
                        'name' => 'Shosuro Hyobu',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                    ],
            ],
        ];
        
        assertSame($expectedArgs, $args);
    }

    // ----------------------------------------------------------------------
    public function testEnteringState_Draft_GoToNextPlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_NEXT_PLAYER;
        $game->stDraftNextPlayer();
        
        //let next player play
        assertSame(ST_DRAFT_PLAYER, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_Draft_end(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        GamestateMachine::$test_current_state = ST_DRAFT_NEXT_PLAYER;
        $game->stDraftNextPlayer();
        
        //Continue to next state
        assertSame(ST_PLAYER_SETUP, GamestateMachine::$test_current_state);
    }

    public function testEnteringState_Draft_autoAssignLastCard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DISCARD;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DISCARD;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_NEXT_PLAYER;
        $game->stDraftNextPlayer();
        
        //Continue to next state
        assertSame(ST_PLAYER_SETUP, GamestateMachine::$test_current_state);
    }

    
    // ----------------------------------------------------------------------
    public function test_actTakeCard_KO_card_doesnotexist(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 99999;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $this->expectException(feException::class);
        $this->expectExceptionMessage("Class Pieces: getMany, some pieces have not been found ! Table cards [$cardId]");
        $game->actTakeCard($cardId);
    }
    public function test_actTakeCard_KO_card_notselectable(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $game->actTakeCard($cardId);
        
    }
    
    public function test_actTakeCard_KO_selectableForOtherPlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 2;
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $game->actTakeCard($cardId);
        
    }

    public function test_actTakeCard_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $game->actTakeCard($cardId);
        
        assertSame(ST_DRAFT_NEXT_PLAYER, GamestateMachine::$test_current_state);
    }
    
    public function test_actTakeCard_Pass_Multi(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;

        $game->actTakeCard($cardId);
        
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
    }
    
    public function test_actTakeCard_Pass_ScionOfVoid(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        $cardId = 101;
        TestDatas::$cards[$cardId]['type'] = PATRON_SCION_OF_VOID;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $game->actTakeCard($cardId);
        
        assertSame(TILE_LOCATION_MASTERY_RESERVED, TestDatas::$tiles[4]['tile_location']);
        assertSame(TILE_LOCATION_MASTERY_RESERVED, TestDatas::$tiles[5]['tile_location']);
        assertSame(TILE_LOCATION_MASTERY_RESERVED, TestDatas::$tiles[6]['tile_location']);
        assertSame(1, TestDatas::$tiles[4]['player_id']);
        assertSame(1, TestDatas::$tiles[5]['player_id']);
        assertSame(1, TestDatas::$tiles[6]['player_id']);
        assertSame(4, TestDatas::$tiles[4]['type']);
        assertSame(5, TestDatas::$tiles[5]['type']);
        assertSame(6, TestDatas::$tiles[6]['type']);
    }

    // ----------------------------------------------------------------------

    public function testArgs_DraftMulti(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;
        $args = $game->argDraftMulti();
        $expectedArgs = [
            '_private' => [ 
                1 => [
                    'cards' => [ 
                        [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],

                    ],
                ],
                2 => [
                    'cards' => [ 
                        [
                            'id' => 103,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_LADY,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Lady of Whispers',
                            'name' => 'Bayushi Kashiko',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 104,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_GOVERNOR,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Governor of the City of lies',
                            'name' => 'Shosuro Hyobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                    ],
                ],
            ],
        ];
        
        assertSame($expectedArgs, $args);
    }

    // ----------------------------------------------------------------------

    public function testEnteringState_DraftMulti(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;
        $game->stDraftMulti();
        
        //STILL SAME STATE until players play
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
    }
    // ----------------------------------------------------------------------
     
}