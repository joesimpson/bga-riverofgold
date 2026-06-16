<?php

declare(strict_types=1);

namespace Tests;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UserException;
use ROG\Managers\Players;
use ROG\Models\ScenarioType;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class GameTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    public function test_getAllDatas(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $expectedDatas = [
            'prefs' => [],
            'players' => [
                1 => [
                    'id' => 1,
                    'no' => 2,
                    'name' => 'Player_NAME_1',
                    'color' => 'ff0000',
                    'eliminated' => null,
                    'score' => 19,
                    'scoreAux' => 0,
                    'zombie' => null,
                    'die' => 1,
                    'clan' => 1,
                    'lastTurnPlayed' => false,
                    'skipRollDie' => false,
                    'money' => 0,
                    'silk' => 0,
                    'rice' => 0,
                    'pottery' => 0,
                    'moon' => 3,
                    'sun' => 0,
                    'buildings' => [
                        1 => 1,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                    ],
                    'influence' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                    ],
                    'customers' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                        7 => 0,
                        8 => 0,
                        9 => 0,
                        10 => 0,
                    ],
                ],
                2 => [
                    'id' => 2,
                    'no' => 3,
                    'name' => 'Player_NAME_2',
                    'color' => 'ffffff',
                    'eliminated' => null,
                    'score' => 2,
                    'scoreAux' => 0,
                    'zombie' => null,
                    'die' => 3,
                    'clan' => 2,
                    'lastTurnPlayed' => false,
                    'skipRollDie' => false,
                    'money' => 0,
                    'silk' => 0,
                    'rice' => 0,
                    'pottery' => 0,
                    'moon' => 3,
                    'sun' => 0,
                    'buildings' => [
                        1 => 1,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                    ],
                    'influence' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                    ],
                    'customers' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                        7 => 0,
                        8 => 0,
                        9 => 0,
                        10 => 0,
                    ],
                ],
            ],
            'cards' => [
                0 => [
                    'id' => 11,
                    'state' => 0,
                    'location' => 'h',
                    'pId' => 1,
                    'type' => 1,
                    'customerType' => 1,
                    'region' => 1,
                    'cost' => [
                        2 => 2,
                    ],
                    'title' => 'Artisan',
                    'desc' => '',
                    'subtype' => 1,
                    'monkType' => null,
                ],
                1 => [
                    'id' => 13,
                    'state' => 0,
                    'location' => 'h',
                    'pId' => 1,
                    'type' => 3,
                    'customerType' => 1,
                    'region' => 3,
                    'cost' => [
                        1 => 2,
                    ],
                    'title' => 'Artisan',
                    'desc' => '',
                    'subtype' => 1,
                    'monkType' => null,
                ],
            ],
            'tiles' => [
                [
                    'id' => 11,
                    'location' => 's',
                    'type' => 4,
                    'scores' => [
                        0 => 8,
                        1 => 4,
                    ],
                    'pos' => 1,
                    'subtype' => 1,
                    'maxSpaces' => 5,
                ],
                [
                    'id' => 12,
                    'location' => 's',
                    'type' => 5,
                    'scores' => [
                        0 => 6,
                        1 => 3,
                    ],
                    'pos' => 2,
                    'subtype' => 1,
                    'maxSpaces' => 5,
                ],
                [
                    'id' => 13,
                    'location' => 's',
                    'type' => 3,
                    'scores' =>[
                        0 => 4,
                        1 => 2,
                    ],
                    'pos' => 3,
                    'subtype' => 1,
                    'maxSpaces' => 5,
                ],
                [
                    'id' => 14,
                    'location' => 's',
                    'type' => 2,
                    'scores' => [
                        0 => 5,
                        1 => 2,
                    ],
                    'pos' => 4,
                    'subtype' => 1,
                    'maxSpaces' => 5,
                ],
                [
                    'id' => 15,
                    'location' => 's',
                    'type' => 1,
                    'scores' => [
                        0 => 3,
                    ],
                    'pos' => 5,
                    'subtype' => 1,
                    'maxSpaces' => null,
                ],
                [
                    'id' => 16,
                    'location' => 's',
                    'type' => 6,
                    'scores' => [
                        0 => 7,
                        1 => 3,
                    ],
                    'pos' => 6,
                    'subtype' => 1,
                    'maxSpaces' => 5,
                ],
                [
                    'id' => 1,
                    'location' => 'm',
                    'type' => 1,
                    'pId' => null,
                    'scoringType' => 1,
                    'nbPlayers' => [
                        0 => 2,
                    ],
                    'title' => 'Mastery of Air',
                    'subtype' => 3,
                ],
                [
                    'id' => 2,
                    'location' => 'm',
                    'type' => 2,
                    'pId' => null,
                    'scoringType' => 2,
                    'nbPlayers' => [
                        0 => 2,
                    ],
                    'title' => 'Mastery of the Courts',
                    'subtype' => 3,
                ],
                [
                    'id' => 3,
                    'location' => 'm',
                    'type' => 3,
                    'pId' => null,
                    'scoringType' => 3,
                    'nbPlayers' => [
                        0 => 2,
                    ],
                    'title' => 'Mastery of Earth',
                    'subtype' => 3,
                ],
                [
                    'id' => 31,
                    'location' => 'br',
                    'type' => 2,
                    'bonus' => 1,
                    'buildingType' => 1,
                    'era' => 1,
                    'pos' => 1,
                    'subtype' => 2,
                    'ownerReward' => [
                        'entries' => [
                            0 => [
                                'type' => 3,
                                'n' => 1,
                            ],
                        ],
                    ],
                    'visitorReward' =>[
                        'entries' =>  [
                            0 => [
                                'type' => 6,
                                'n' => 3,
                            ],
                        ],
                    ],
                ],
                 [
                    'id' => 32,
                    'location' => 'br',
                    'type' => 3,
                    'bonus' => 0,
                    'buildingType' => 1,
                    'era' => 1,
                    'pos' => 2,
                    'subtype' => 2,
                    'ownerReward' => [
                        'entries' => [
                            0 => [
                                'type' => 6,
                                'n' => 1,
                            ],
                            1 => [
                                'type' => 2,
                                'n' => 1,
                            ],
                        ],
                    ],
                    'visitorReward' =>  [
                        'entries' =>  [
                            0 => [
                                'type' => 6,
                                'n' => 3,
                            ],
                        ],
                    ],
                ],
                 [
                    'id' => 33,
                    'location' => 'br',
                    'type' => 8,
                    'bonus' => 4,
                    'buildingType' => 2,
                    'era' => 1,
                    'pos' => 3,
                    'subtype' => 2,
                    'ownerReward' =>  [
                        'entries' =>  [
                            0 =>  [
                                'type' => 6,
                                'n' => 2,
                            ],
                        ],
                    ],
                    'visitorReward' =>  [
                        'entries' =>  [
                            0 =>  [
                                'type' => 1,
                                'n' => 1,
                            ],
                        ],
                    ],
                ],
                 [
                    'id' => 34,
                    'location' => 'br',
                    'type' => 14,
                    'bonus' => 1,
                    'buildingType' => 3,
                    'era' => 1,
                    'pos' => 4,
                    'subtype' => 2,
                    'ownerReward' =>  [
                        'entries' =>  [
                            0 =>  [
                                'type' => 1,
                                'n' => 1,
                            ],
                        ],
                    ],
                    'visitorReward' =>  [
                        'entries' => [
                            0 => [
                                'type' => 22,
                                'n' => 2,
                            ],
                        ],
                    ],
                ],
                 [
                    'id' => 41,
                    'location' => 'sh',
                    'type' => 5,
                    'bonus' => 3,
                    'buildingType' => 1,
                    'era' => 1,
                    'pos' => 4,
                    'subtype' => 2,
                    'ownerReward' =>  [
                        'entries' =>  [
                            0 =>  [
                                'type' => 30,
                                'n' => 1,
                            ],
                        ],
                    ],
                    'visitorReward' =>  [
                        'entries' =>  [
                            0 =>  [
                                'type' => 6,
                                'n' => 3,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 42,
                    'location' => 'sh',
                    'type' => 6,
                    'bonus' => 3,
                    'buildingType' => 1,
                    'era' => 1,
                    'pos' => 5,
                    'subtype' => 2,
                    'ownerReward' =>  [
                        'entries' =>  [
                            0 => [
                                'type' => 20,
                                'n' => 1,
                            ],
                        ],
                    ],
                    'visitorReward' => [
                        'entries' => [
                            0 =>  [
                                'type' => 6,
                                'n' => 3,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 44,
                    'location' => 'sh',
                    'type' => 44,
                    'bonus' => 0,
                    'buildingType' => BUILDING_TYPE_MARKET,
                    'era' => 0,
                    'pos' => 6,
                    'subtype' => TILE_TYPE_BUILDING,
                    'ownerReward' =>  [
                        'entries' =>  [
                        ],
                    ],
                    'visitorReward' => [
                        'entries' => [
                            [
                                'type' => RESOURCE_TYPE_POTTERY,
                                'n' => 1,
                            ],
                            [
                                'type' => RESOURCE_TYPE_SUN,
                                'n' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 45,
                    'location' => 'sh',
                    'type' => 45,
                    'bonus' => 0,
                    'buildingType' => BUILDING_TYPE_MARKET,
                    'era' => 0,
                    'pos' => 17,
                    'subtype' => TILE_TYPE_BUILDING,
                    'ownerReward' =>  [
                        'entries' =>  [
                        ],
                    ],
                    'visitorReward' => [
                        'entries' => [
                            [
                                'type' => RESOURCE_TYPE_RICE,
                                'n' => 1,
                            ],
                            [
                                'type' => RESOURCE_TYPE_SUN,
                                'n' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 46,
                    'location' => 'sh',
                    'type' => 46,
                    'bonus' => 0,
                    'buildingType' => BUILDING_TYPE_MARKET,
                    'era' => 0,
                    'pos' => 29,
                    'subtype' => TILE_TYPE_BUILDING,
                    'ownerReward' =>  [
                        'entries' =>  [
                        ],
                    ],
                    'visitorReward' => [
                        'entries' => [
                            [
                                'type' => RESOURCE_TYPE_SILK,
                                'n' => 1,
                            ],
                            [
                                'type' => BONUS_TYPE_DRAW,
                                'n' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 21,
                    'location' => 'bd1',
                    'type' => 1,
                    'bonus' => 0,
                    'buildingType' => 1,
                    'era' => 0,
                    'pos' => 0,
                    'subtype' => 2,
                    'ownerReward' => [
                        'entries' => [],
                    ],
                    'visitorReward' => [
                        'entries' => [
                            0 => [
                                'type' => 6,
                                'n' => 3,
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 101,
                    'location' => 'bd2',
                    'type' => 11,
                    'bonus' => 3,
                    'buildingType' => 2,
                    'era' => 1,
                    'pos' => 0,
                    'subtype' => 2,
                    'ownerReward' => [
                        'entries' => [
                            0 => [
                                'type' => 20,
                                'n' => 1,
                            ],
                        ],
                    ],
                    'visitorReward' => [
                        'entries' => [
                            0 => [
                                'type' => 1,
                                'n' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            
            'meeples' =>  [
                0 =>  [
                    'id' => 1,
                    'location' => 'i-1',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 0,
                ],
                1 =>  [
                    'id' => 2,
                    'location' => 'i-2',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 0,
                ],
                2 =>  [
                    'id' => 3,
                    'location' => 'i-3',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 0,
                ],
                3 =>  [
                    'id' => 4,
                    'location' => 'i-4',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 0,
                ],
                4 =>  [
                    'id' => 5,
                    'location' => 'i-5',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 0,
                ],
                5 =>  [
                    'id' => 6,
                    'location' => 'i-6',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 0,
                ],
                6 =>  [
                    'id' => 11,
                    'location' => 'i-1',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 0,
                ],
                7 =>  [
                    'id' => 12,
                    'location' => 'i-2',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 0,
                ],
                8 =>  [
                    'id' => 13,
                    'location' => 'i-3',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 0,
                ],
                9 =>  [
                    'id' => 14,
                    'location' => 'i-4',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 0,
                ],
                10 =>  [
                    'id' => 15,
                    'location' => 'i-5',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 0,
                ],
                11 =>  [
                    'id' => 16,
                    'location' => 'i-6',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 0,
                ],
                12 =>  [
                    'id' => 21,
                    'location' => 'r',
                    'pId' => 1,
                    'type' => 1,
                    'pos' => 5,
                ],
                13 =>  [
                    'id' => 22,
                    'location' => 'r',
                    'pId' => 1,
                    'type' => 1,
                    'pos' => 14,
                ],
                14 =>  [
                    'id' => 23,
                    'location' => 'r',
                    'pId' => 2,
                    'type' => 1,
                    'pos' => 3,
                ],
                15 =>  [
                    'id' => 24,
                    'location' => 'r',
                    'pId' => 2,
                    'type' => 1,
                    'pos' => 14,
                ],
                [
                    'id' => 25,
                    'location' => 'r',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 1,
                    'pos' => 11,
                ],
                [
                    'id' => 26,
                    'location' => 'r',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 1,
                    'pos' => 12,
                ],
                [
                    'id' => 31,
                    'location' => 'i-1',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 2,
                    'pos' => 0,
                ],
                [
                    'id' => 32,
                    'location' => 'i-2',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 2,
                    'pos' => 0,
                ],
                [
                    'id' => 33,
                    'location' => 'i-3',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 2,
                    'pos' => 0,
                ],
                [
                    'id' => 34,
                    'location' => 'i-4',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 2,
                    'pos' => 0,
                ],
                [
                    'id' => 35,
                    'location' => 'i-5',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 2,
                    'pos' => 0,
                ],
                [
                    'id' => 36,
                    'location' => 'i-6',
                    'pId' => AUTOMA_PLAYER_ID,
                    'type' => 2,
                    'pos' => 0,
                ],
                [
                    'id' => 41,
                    'location' => 'tile-41',
                    'pId' => 1,
                    'type' => 2,
                    'pos' => 1,
                ],
                [
                    'id' => 42,
                    'location' => 'tile-42',
                    'pId' => 2,
                    'type' => 2,
                    'pos' => 1,
                ],
            ],
            'turn' => 1,
            'era' => 1,
            'deckSize' => [
                'era1' => 6,
                'era2' => 6,
                'customers' => 3,
                'customerDiscard' => 0,
                'automaDeck' => 9,
                'automaPlayed' => 0,
                'hiddenDeliv' => [
                ],
            ],
            'firstPlayer' => 1,
            'endTriggered' => false,
            'endScoring' => [],
            'customerTypes' => [1,2,3,4,5],
            'customTracks' => null,
            'automa_level' => 0,
            'version' => 999999,
            'constants' => [
                'INFLUENCE_TRACK_REWARDS' => INFLUENCE_TRACK_REWARDS,
                'CUSTOM_REGION_TRACKS' => CUSTOM_REGION_TRACKS,
                'ROGUE_PLAYER_ID' => ROGUE_PLAYER_ID,
                'SCORPION_ENEMY_ID' => SCORPION_ENEMY_ID,
            ],
            'enums' => [
                'AutomaActionType' => [
                    'SAIL_HIGHER'    => 1,
                    'SAIL_LOWER'     => 2,
                    'DELIVER'        => 3,
                    'BUILD'          => 4,
                    'ADVANCE_CITY'   => 5,
                ],
                'ScenarioType' => [
                    'CRAB_1'    => 1,
                    'MANTIS_1'  => 2,
                    'CRANE_1'   => 3,
                    'SCORPION_1'=> 4,
                    'PHOENIX_1' => 5,
                    'LION_1'    => 6,
                    'DRAGON_1'  => 7,
                    'UNICORN_1' => 8,
                ],
            ],
            'virtual_players' => [],
        ];

        $datas = $game->getAllDatas();
        
        assertSame($expectedDatas, $datas);
    }
    public function test_getAllDatas_withAutoma(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaClan(CLAN_DRAGON);
        Globals::setAutomaDie(REGION_3);
        Globals::setAutomaScore(55);
        Globals::setAutomaLastTurnPlayed(false);
        $expectedAutomaPlayerDatas = [
                    'id' => AUTOMA_PLAYER_ID,
                    'no' => 4,
                    'name' => 'Seishin',
                    'color' =>  '298a47',
                    'score' => 55,
                    'die' => 3,
                    'clan' => 7,
                    'lastTurnPlayed' => false,
                    'money' => 0,
                    'silk' => 0,
                    'rice' => 0,
                    'pottery' => 0,
                    'moon' => 0,
                    'sun' => 0,
                    'buildings' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                    ],
                    'influence' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                    ],
                    'customers' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                        5 => 0,
                        6 => 0,
                        7 => 0,
                        8 => 0,
                        9 => 0,
                        10 => 0,
                    ],
                    'is_automa' => true,
                ];

        $datas = $game->getAllDatas();
        
        assertSame($expectedAutomaPlayerDatas, $datas['players'][AUTOMA_PLAYER_ID]);
        assertSame($expectedAutomaPlayerDatas, $datas['automa_player']);
    }
    
    public function test_getAllDatas_withScenarios(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[302] = ['result_associative_index' => 302,'card_id' => 302, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::SCORPION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        Globals::setRogueClan(CLAN_UNICORN);
        Globals::setScorpionEnemy(CLAN_LION);
        $expectedVirtualPlayers = [
            ROGUE_PLAYER_ID => [
                'id' => ROGUE_PLAYER_ID, 
                'clan' => CLAN_UNICORN, 
                'color' => '982fff', 
                'name' => 'Rogue pirate', 
            ],
            SCORPION_ENEMY_ID => [
                'id' => SCORPION_ENEMY_ID, 
                'clan' => CLAN_LION, 
                'color' => 'ffff00', 
                'name' => 'Scorpion target', 
            ],
        ];

        $datas = $game->getAllDatas();
        
        assertSame($expectedVirtualPlayers, $datas['virtual_players']);
    }
    // -------------------------------------------------
    
    public function test_checkVersion_pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $clientVersion = 999999;

        $game->checkVersion($clientVersion);

        //Check no exception
        assertSame(1,1);
    }
    public function test_checkVersion_KO_Newer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $clientVersion = 9999991;

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $game->checkVersion($clientVersion);
    }
    public function test_checkVersion_KO_Older(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $clientVersion = 19;

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $game->checkVersion($clientVersion);
    }
    // -------------------------------------------------
    
    public function test_getGameProgression_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 8 in deck 1, 9 in deck 2, 4 in building row 
        TestDatas::$tiles[27] = TestDatas::$tiles[21];
        TestDatas::$tiles[28] = TestDatas::$tiles[21];
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            //copy tiles in deck 2 until good number 
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame(0,$progress);
    }
    public function test_getGameProgression_1_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 7 in deck 1, 9 in deck 2, 4 in building row 
        TestDatas::$tiles[27] = TestDatas::$tiles[21];
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            //copy tiles in deck 2 until good number 
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 1/21 * 100,$progress);
    }
    public function test_getGameProgression_2_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 6 in deck 1, 9 in deck 2, 4 in building row 
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            //copy tiles in deck 2 until good number 
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 2/21 * 100,$progress);
    }
    public function test_getGameProgression_3_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 5 in deck 1, 9 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 3/21 * 100,$progress);
    }
    public function test_getGameProgression_4_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 4 in deck 1, 9 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 4/21 * 100,$progress);
    }
    public function test_getGameProgression_5_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 3 in deck 1, 9 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 5/21 * 100,$progress);
    }
    public function test_getGameProgression_6_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 2 in deck 1, 9 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 6/21 * 100,$progress);
    }
    public function test_getGameProgression_7_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 1 in deck 1, 9 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 7/21 * 100,$progress);
    }
    public function test_getGameProgression_8_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 0 in deck 1, 9 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 8/21 * 100,$progress);
    }
    public function test_getGameProgression_9_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 0 in deck 1, 8 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4 - 1;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 9/21 * 100,$progress);
    }
    public function test_getGameProgression_10_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 0 in deck 1, 7 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4 - 2;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 10/21 * 100,$progress);
    }
    public function test_getGameProgression_11_21(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 0 in deck 1, 6 in deck 2, 4 in building row 
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        for($k=1;$k<=21 - 6 - 6 - 4 - 3;$k++){
            TestDatas::$tiles[106+$k] = TestDatas::$tiles[101];
        }

        $progress = $game->getGameProgression();

        assertSame( 11/21 * 100,$progress);
    }
    public function test_getGameProgression_max(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //Datas : 0 in deck 1, 0 in deck 2, 3 in building row for 2 players
        unset(TestDatas::$tiles[21]);
        unset(TestDatas::$tiles[22]);
        unset(TestDatas::$tiles[23]);
        unset(TestDatas::$tiles[24]);
        unset(TestDatas::$tiles[25]);
        unset(TestDatas::$tiles[26]);
        unset(TestDatas::$tiles[34]);
        unset(TestDatas::$tiles[101]);
        unset(TestDatas::$tiles[102]);
        unset(TestDatas::$tiles[103]);
        unset(TestDatas::$tiles[104]);
        unset(TestDatas::$tiles[105]);
        unset(TestDatas::$tiles[106]);
        
        $progress = $game->getGameProgression();

        assertSame(19/21 * 100,$progress);
    }
    // -------------------------------------------------
    
    public function test_ZombieTurn_Draft(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        $state = [ 'type' => 'activeplayer', 'name'=> 'draft'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_DRAFT_NEXT_PLAYER, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_DraftMulti(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;
        $state = [ 'type' => 'multipleactiveplayer', 'name'=> 'draftMulti'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BeforeTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BEFORE_TURN;
        $state = [ 'type' => 'activeplayer', 'name'=> 'beforeTurn'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_PlayerTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        $state = [ 'type' => 'activeplayer', 'name'=> 'playerTurn'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_PlayerTurn_Build(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        $state = [ 'type' => 'activeplayer', 'name'=> 'build'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BonusChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusChoice'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
        assertSame('[]', TestDatas::$players[1]['bonuses']);
    }
    public function test_ZombieTurn_BonusChoiceResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusResource'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BonusUpgradeShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_UPGRADE_SHIP;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusUpgrade'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BonusSecondMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SECOND_MARKER_ON_BUILDING;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusSecondMarker'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BonusMoneyorGood(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_MONEY_OR_GOOD;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusMoneyGood'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BonusSetDie(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SET_DIE;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusSetDie'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_BonusSellGoods(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        $state = [ 'type' => 'activeplayer', 'name'=> 'bonusSellGoods'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_PlayerTurn_Sail(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        $state = [ 'type' => 'activeplayer', 'name'=> 'sail'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_PlayerTurn_Deliver(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $state = [ 'type' => 'activeplayer', 'name'=> 'deliver'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_PlayerTurn_Trade(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        $state = [ 'type' => 'activeplayer', 'name'=> 'trade'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    public function test_ZombieTurn_PlayerTurn_DivineFavor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DIVINE_FAVOR;
        $state = [ 'type' => 'activeplayer', 'name'=> 'spendFavor'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }

    public function test_ZombieTurn_DiscardCard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_DISCARD_CARD;
        $state = [ 'type' => 'activeplayer', 'name'=> 'discardCard'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }

    public function test_ZombieTurn_ConfirmTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        $state = [ 'type' => 'activeplayer', 'name'=> 'confirmTurn'];

        $game->zombieTurn($state, TestDatas::$test_activePlayerId );
        
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
}