<?php

declare(strict_types=1);

namespace Tests;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Exceptions\UserException;

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
                    'no' => null,
                    'name' => 'Player_NAME_1',
                    'color' => 'ff0000',
                    'eliminated' => null,
                    'score' => 19,
                    'scoreAux' => null,
                    'zombie' => null,
                    'die' => 1,
                    'clan' => 1,
                    'lastTurnPlayed' => false,
                    'skipRollDie' => false,
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
                    ],
                ],
                2 => [
                    'id' => 2,
                    'no' => null,
                    'name' => 'Player_NAME_2',
                    'color' => 'ffffff',
                    'eliminated' => null,
                    'score' => 2,
                    'scoreAux' => null,
                    'zombie' => null,
                    'die' => 3,
                    'clan' => 2,
                    'lastTurnPlayed' => false,
                    'skipRollDie' => false,
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
                0 => [
                    'id' => 1,
                    'location' => 'm',
                    'type' => 1,
                    'scoringType' => 1,
                    'nbPlayers' => [
                        0 => 2,
                    ],
                    'title' => 'Mastery of Air',
                    'subtype' => 3,
                ],
                1 => [
                    'id' => 2,
                    'location' => 'm',
                    'type' => 2,
                    'scoringType' => 2,
                    'nbPlayers' => [
                        0 => 2,
                    ],
                    'title' => 'Mastery of the Courts',
                    'subtype' => 3,
                ],
                2 => [
                    'id' => 3,
                    'location' => 'm',
                    'type' => 3,
                    'scoringType' => 3,
                    'nbPlayers' => [
                        0 => 2,
                    ],
                    'title' => 'Mastery of Earth',
                    'subtype' => 3,
                ],
                3 => [
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
                4 => [
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
            'meeples' => [],
            'turn' => 1,
            'era' => 1,
            'deckSize' => [
                'era1' => 6,
                'era2' => 6,
            ],
            'firstPlayer' => 1,
            'endTriggered' => false,
            'endScoring' => [],
            'version' => 999999,
        ];

        $datas = $game->getAllDatas();
        
        assertSame($expectedDatas, $datas);
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
    
    public function test_getGameProgression(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        $progress = $game->getGameProgression();

        //TODO : play with tiles and test different progressions
        assertSame(33.33333333333333,$progress);
    }
    // -------------------------------------------------
}