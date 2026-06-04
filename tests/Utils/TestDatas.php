<?php

declare(strict_types=1);

namespace Tests\Utils;

use ROG\Models\AutomaActionType;

class TestDatas {
    static int $test_activePlayerId = 1;
    static int $lastInsertedId = 1;
    static array $players = [
    ];
    public static function resetPlayers(){
        TestDatas::$players = [
            1 => [
                'result_associative_index' => 1,
                'player_id' => 1, 
                'player_color' => 'ff0000' ,
                'player_name' => 'Player_NAME_1', 
                'player_score' => 19, 
                'player_score_aux' => 0, 
                'die_face' => 1, 
                'resources' => '{"1":0,"2":0,"3":0,"4":3,"5":0,"6":0}', 
                'player_clan' => 1, 
                'bonuses' => '[]', 
                'last_turn_played' => false, 
                'skip_roll_die' => false,
            ],
            2 => [
                'result_associative_index' => 2,
                'player_id' => 2, 
                'player_color' => 'ffffff' ,
                'player_name' => 'Player_NAME_2', 
                'player_score' => 2, 
                'player_score_aux' => 0, 
                'die_face' => 3, 
                'resources' => '{"1":0,"2":0,"3":0,"4":3,"5":0,"6":0}', 
                'player_clan' => 2, 
                'bonuses' => '[]', 
                'last_turn_played' => false, 
                'skip_roll_die' => false,
            ],
        ];
    }
    static array $tokens = [
    ];
    public static function resetTokens(){
        TestDatas::$tokens = [
            1 => ['result_associative_index' => 1, 'meeple_id' => 1, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            2 => ['result_associative_index' => 2, 'meeple_id' => 2, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            3 => ['result_associative_index' => 3, 'meeple_id' => 3, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            4 => ['result_associative_index' => 4, 'meeple_id' => 4, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'4','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            5 => ['result_associative_index' => 5, 'meeple_id' => 5, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            6 => ['result_associative_index' => 6, 'meeple_id' => 6, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            
            11 => ['result_associative_index' => 11, 'meeple_id' => 11, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
            12 => ['result_associative_index' => 12, 'meeple_id' => 12, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
            13 => ['result_associative_index' => 13, 'meeple_id' => 13, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
            14 => ['result_associative_index' => 14, 'meeple_id' => 14, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'4','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
            15 => ['result_associative_index' => 15, 'meeple_id' => 15, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
            16 => ['result_associative_index' => 16, 'meeple_id' => 16, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
              
            21 => ['result_associative_index' => 21, 'meeple_id' => 21, 'meeple_state' => 5, 'meeple_location'=> MEEPLE_LOCATION_RIVER,'type' => MEEPLE_TYPE_SHIP,  'player_id' => 1,  ],
            22 => ['result_associative_index' => 22, 'meeple_id' => 22, 'meeple_state' => 14, 'meeple_location'=> MEEPLE_LOCATION_RIVER,'type' => MEEPLE_TYPE_SHIP,  'player_id' => 1,  ],
            23 => ['result_associative_index' => 23, 'meeple_id' => 23, 'meeple_state' => 3, 'meeple_location'=> MEEPLE_LOCATION_RIVER,'type' => MEEPLE_TYPE_SHIP,  'player_id' => 2,  ],
            24 => ['result_associative_index' => 24, 'meeple_id' => 24, 'meeple_state' => 14, 'meeple_location'=> MEEPLE_LOCATION_RIVER,'type' => MEEPLE_TYPE_SHIP,  'player_id' => 2,  ],
            25 => ['result_associative_index' => 25, 'meeple_id' => 25, 'meeple_state' => 11, 'meeple_location'=> MEEPLE_LOCATION_RIVER,'type' => MEEPLE_TYPE_SHIP,  'player_id' => AUTOMA_PLAYER_ID,  ],
            26 => ['result_associative_index' => 26, 'meeple_id' => 26, 'meeple_state' => 12, 'meeple_location'=> MEEPLE_LOCATION_RIVER,'type' => MEEPLE_TYPE_SHIP,  'player_id' => AUTOMA_PLAYER_ID,  ],
            
            31 => ['result_associative_index' => 31, 'meeple_id' => 31, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ],
            32 => ['result_associative_index' => 32, 'meeple_id' => 32, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ],
            33 => ['result_associative_index' => 33, 'meeple_id' => 33, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ],
            34 => ['result_associative_index' => 34, 'meeple_id' => 34, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'4','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ],
            35 => ['result_associative_index' => 35, 'meeple_id' => 35, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ],
            36 => ['result_associative_index' => 36, 'meeple_id' => 36, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID,  ],
            
            41 => ['result_associative_index' => 41, 'meeple_id' => 41, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'41','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
            42 => ['result_associative_index' => 42, 'meeple_id' => 42, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'42','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],

            //only set on tests which uses it
            //101 => ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
        ];
    }

    static array $cards = [
    ];

    public static function resetCards(){
        TestDatas::$cards = [
            1 => ['result_associative_index' => 1,'card_id' => 1, 'card_location' => CARD_LOCATION_DECK, 'card_state' => 3, 'player_id' => null, 'type' => CARD_ELDER_1, 'subtype' => CARD_TYPE_CUSTOMER,],
            2 => ['result_associative_index' => 2,'card_id' => 2, 'card_location' => CARD_LOCATION_DECK, 'card_state' => 2, 'player_id' => null, 'type' => CARD_NOBLE_1, 'subtype' => CARD_TYPE_CUSTOMER,],
            3 => ['result_associative_index' => 3,'card_id' => 3, 'card_location' => CARD_LOCATION_DECK, 'card_state' => 1, 'player_id' => null, 'type' => CARD_ARTISAN_3, 'subtype' => CARD_TYPE_CUSTOMER,],

            11 => ['result_associative_index' => 11,'card_id' => 11, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 1, 'type' => CARD_ARTISAN_1, 'subtype' => CARD_TYPE_CUSTOMER,],
            12 => ['result_associative_index' => 12,'card_id' => 12, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 2, 'type' => CARD_ARTISAN_2, 'subtype' => CARD_TYPE_CUSTOMER,],
            13 => ['result_associative_index' => 13,'card_id' => 13, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 1, 'type' => CARD_ARTISAN_3, 'subtype' => CARD_TYPE_CUSTOMER,],
            
            101 => ['result_associative_index' => 101,'card_id' => 101, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 1, 'type' => PATRON_MASTER_ENGINEER, 'subtype' => CARD_TYPE_CLAN_PATRON,],
            102 => ['result_associative_index' => 102,'card_id' => 102, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 1, 'type' => PATRON_TRADER, 'subtype' => CARD_TYPE_CLAN_PATRON,],
            103 => ['result_associative_index' => 103,'card_id' => 103, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 2, 'type' => PATRON_LADY, 'subtype' => CARD_TYPE_CLAN_PATRON,],
            104 => ['result_associative_index' => 104,'card_id' => 104, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 2, 'type' => PATRON_GOVERNOR, 'subtype' => CARD_TYPE_CLAN_PATRON,],

            201 => ['result_associative_index' => 201,'card_id' => 201, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 1, 'player_id' => null, 'type' => AutomaActionType::SAIL_HIGHER->value,   'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            202 => ['result_associative_index' => 202,'card_id' => 202, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 2, 'player_id' => null, 'type' => AutomaActionType::SAIL_HIGHER->value,   'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            203 => ['result_associative_index' => 203,'card_id' => 203, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 3, 'player_id' => null, 'type' => AutomaActionType::SAIL_HIGHER->value,   'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            204 => ['result_associative_index' => 204,'card_id' => 204, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 4, 'player_id' => null, 'type' => AutomaActionType::SAIL_LOWER->value,    'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            205 => ['result_associative_index' => 205,'card_id' => 205, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 5, 'player_id' => null, 'type' => AutomaActionType::SAIL_LOWER->value,    'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            206 => ['result_associative_index' => 206,'card_id' => 206, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 6, 'player_id' => null, 'type' => AutomaActionType::DELIVER->value,       'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            207 => ['result_associative_index' => 207,'card_id' => 207, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 7, 'player_id' => null, 'type' => AutomaActionType::BUILD->value,         'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            208 => ['result_associative_index' => 208,'card_id' => 208, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 8, 'player_id' => null, 'type' => AutomaActionType::BUILD->value,         'subtype' => CARD_TYPE_AUTOMA_ACTION,],
            209 => ['result_associative_index' => 209,'card_id' => 209, 'card_location' => CARD_AUTOMA_LOCATION_DECK, 'card_state' => 9, 'player_id' => null, 'type' => AutomaActionType::ADVANCE_CITY->value,  'subtype' => CARD_TYPE_AUTOMA_ACTION,],

        ];
    }
    static array $tiles = [
    ];
    public static function resetTiles(){
        TestDatas::$tiles = [
            1 => ['result_associative_index' => 1,'tile_id' => 1, 'tile_location' => TILE_LOCATION_MASTERY_CARD, 'tile_state' => 0,  'type' => 1, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            2 => ['result_associative_index' => 2,'tile_id' => 2, 'tile_location' => TILE_LOCATION_MASTERY_CARD, 'tile_state' => 1,  'type' => 2, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            3 => ['result_associative_index' => 3,'tile_id' => 3, 'tile_location' => TILE_LOCATION_MASTERY_CARD, 'tile_state' => 2,  'type' => 3, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            4 => ['result_associative_index' => 4,'tile_id' => 4, 'tile_location' => TILE_LOCATION_MASTERY_DECK, 'tile_state' => 1,  'type' => 4, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            5 => ['result_associative_index' => 5,'tile_id' => 5, 'tile_location' => TILE_LOCATION_MASTERY_DECK, 'tile_state' => 2,  'type' => 5, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            6 => ['result_associative_index' => 6,'tile_id' => 6, 'tile_location' => TILE_LOCATION_MASTERY_DECK, 'tile_state' => 3,  'type' => 6, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            7 => ['result_associative_index' => 7,'tile_id' => 7, 'tile_location' => TILE_LOCATION_MASTERY_DECK, 'tile_state' => 4,  'type' => 13, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            8 => ['result_associative_index' => 8,'tile_id' => 8, 'tile_location' => TILE_LOCATION_MASTERY_DECK, 'tile_state' => 5,  'type' => 14, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            9 => ['result_associative_index' => 9,'tile_id' => 9, 'tile_location' => TILE_LOCATION_MASTERY_DECK, 'tile_state' => 6,  'type' => 15, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
            
            11 => ['result_associative_index' => 11,'tile_id' => 11, 'tile_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 4, 'subtype' => TILE_TYPE_SCORING, ],
            12 => ['result_associative_index' => 12,'tile_id' => 12, 'tile_location' => TILE_LOCATION_SCORING, 'tile_state' => 1,  'type' => 5, 'subtype' => TILE_TYPE_SCORING, ],
            13 => ['result_associative_index' => 13,'tile_id' => 13, 'tile_location' => TILE_LOCATION_SCORING, 'tile_state' => 2,  'type' => 3, 'subtype' => TILE_TYPE_SCORING, ],
            14 => ['result_associative_index' => 14,'tile_id' => 14, 'tile_location' => TILE_LOCATION_SCORING, 'tile_state' => 3,  'type' => 2, 'subtype' => TILE_TYPE_SCORING, ],
            15 => ['result_associative_index' => 15,'tile_id' => 15, 'tile_location' => TILE_LOCATION_SCORING, 'tile_state' => 4,  'type' => 1, 'subtype' => TILE_TYPE_SCORING, ],
            16 => ['result_associative_index' => 16,'tile_id' => 16, 'tile_location' => TILE_LOCATION_SCORING, 'tile_state' => 5,  'type' => 6, 'subtype' => TILE_TYPE_SCORING, ],

            21 => ['result_associative_index' => 21,'tile_id' => 21, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 1, 'subtype' => TILE_TYPE_BUILDING, ],
            22 => ['result_associative_index' => 22,'tile_id' => 22, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 2, 'subtype' => TILE_TYPE_BUILDING, ],
            23 => ['result_associative_index' => 23,'tile_id' => 23, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 3, 'subtype' => TILE_TYPE_BUILDING, ],
            24 => ['result_associative_index' => 24,'tile_id' => 24, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 4, 'subtype' => TILE_TYPE_BUILDING, ],
            25 => ['result_associative_index' => 25,'tile_id' => 25, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 5, 'subtype' => TILE_TYPE_BUILDING, ],
            26 => ['result_associative_index' => 26,'tile_id' => 26, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 6, 'subtype' => TILE_TYPE_BUILDING, ],

            31 => ['result_associative_index' => 31,'tile_id' => 31, 'tile_location' => TILE_LOCATION_BUILDING_ROW, 'tile_state' => 1,  'type' => 2, 'subtype' => TILE_TYPE_BUILDING, ],
            32 => ['result_associative_index' => 32,'tile_id' => 32, 'tile_location' => TILE_LOCATION_BUILDING_ROW, 'tile_state' => 2,  'type' => 3, 'subtype' => TILE_TYPE_BUILDING, ],
            33 => ['result_associative_index' => 33,'tile_id' => 33, 'tile_location' => TILE_LOCATION_BUILDING_ROW, 'tile_state' => 3,  'type' => 8, 'subtype' => TILE_TYPE_BUILDING, ],
            34 => ['result_associative_index' => 34,'tile_id' => 34, 'tile_location' => TILE_LOCATION_BUILDING_ROW, 'tile_state' => 4,  'type' => 14, 'subtype' => TILE_TYPE_BUILDING, ],

            41 => ['result_associative_index' => 41,'tile_id' => 41, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 4,  'type' => 5, 'subtype' => TILE_TYPE_BUILDING, ],
            42 => ['result_associative_index' => 42,'tile_id' => 42, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 5,  'type' => 6, 'subtype' => TILE_TYPE_BUILDING, ],
            //SHORE_SPACE_IMPERIAL_MARKET
            44 => ['result_associative_index' => 44,'tile_id' => 44, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 6,  'type' => 44, 'subtype' => TILE_TYPE_BUILDING, ],
            45 => ['result_associative_index' => 45,'tile_id' => 45, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 17,  'type' => 45, 'subtype' => TILE_TYPE_BUILDING, ],
            46 => ['result_associative_index' => 46,'tile_id' => 46, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 29,  'type' => 46, 'subtype' => TILE_TYPE_BUILDING, ],

            101 => ['result_associative_index' => 101,'tile_id' => 101, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 11, 'subtype' => TILE_TYPE_BUILDING, ],
            102 => ['result_associative_index' => 102,'tile_id' => 102, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 12, 'subtype' => TILE_TYPE_BUILDING, ],
            103 => ['result_associative_index' => 103,'tile_id' => 103, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 13, 'subtype' => TILE_TYPE_BUILDING, ],
            104 => ['result_associative_index' => 104,'tile_id' => 104, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 14, 'subtype' => TILE_TYPE_BUILDING, ],
            105 => ['result_associative_index' => 105,'tile_id' => 105, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 15, 'subtype' => TILE_TYPE_BUILDING, ],
            106 => ['result_associative_index' => 106,'tile_id' => 106, 'tile_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 16, 'subtype' => TILE_TYPE_BUILDING, ],
        ];
    }
    
    static array $notifs = [];
    public static function resetNotifications(){
        TestDatas::$notifs = [
            'all' => [
            ],
            1 => [
            ],
            2 => [
            ],
            AUTOMA_PLAYER_ID => [
            ],
        ];
    }

    static array $stats = [];
    public static function resetStats(){
        TestDatas::$stats = [
            'table' => [
                10 => 0,
                14 => 0,
            ],
            1 => [
                'nbActionsBuild' => 0,
                'nbActionsDeliver' => 0,
                'nbActionsSail' => 0,
            ],
            2 => [
                'nbActionsBuild' => 0,
                'nbActionsDeliver' => 0,
                'nbActionsSail' => 0,
            ],
        ];
    }
    
    static array $logs = [];
    public static function resetLogs(){
        TestDatas::$logs = [
            
        ];
    }
}
