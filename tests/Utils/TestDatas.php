<?php

declare(strict_types=1);

namespace Tests\Utils;

class TestDatas {
    static int $test_activePlayerId = 1;
    static array $players = [
        1 => [
            'result_associative_index' => 1,
            'player_id' => 1, 
            'player_color' => 'ff0000' ,
            'player_name' => 'Player_NAME_1', 
            'player_score' => 19, 
            'die_face' => 1, 
            'resources' => '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}', 
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
            'die_face' => 3, 
            'resources' => '[]', 
            'player_clan' => 2, 
            'bonuses' => '[]', 
            'last_turn_played' => false, 
            'skip_roll_die' => false,
        ],
    ];
    static array $tokens = [
        1 => ['result_associative_index' => 1, 'token_id' => 1, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
        2 => ['result_associative_index' => 2, 'token_id' => 2, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
        3 => ['result_associative_index' => 3, 'token_id' => 3, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
        4 => ['result_associative_index' => 4, 'token_id' => 4, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'4','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
        5 => ['result_associative_index' => 5, 'token_id' => 5, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
        6 => ['result_associative_index' => 6, 'token_id' => 6, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ],
        
        11 => ['result_associative_index' => 11, 'token_id' => 11, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
        12 => ['result_associative_index' => 12, 'token_id' => 12, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
        13 => ['result_associative_index' => 13, 'token_id' => 13, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
        14 => ['result_associative_index' => 14, 'token_id' => 14, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'4','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
        15 => ['result_associative_index' => 15, 'token_id' => 15, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
        16 => ['result_associative_index' => 16, 'token_id' => 16, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ],
            
    ];
    static array $cards = [
        1 => ['result_associative_index' => 1,'card_id' => 1, 'card_location' => CARD_LOCATION_DECK, 'card_state' => 0, 'player_id' => null, 'type' => CARD_ELDER_1, 'subtype' => CARD_TYPE_CUSTOMER,],
        2 => ['result_associative_index' => 2,'card_id' => 2, 'card_location' => CARD_LOCATION_DECK, 'card_state' => 0, 'player_id' => null, 'type' => CARD_NOBLE_1, 'subtype' => CARD_TYPE_CUSTOMER,],
        3 => ['result_associative_index' => 3,'card_id' => 3, 'card_location' => CARD_LOCATION_DECK, 'card_state' => 0, 'player_id' => null, 'type' => CARD_ARTISAN_3, 'subtype' => CARD_TYPE_CUSTOMER,],

        11 => ['result_associative_index' => 11,'card_id' => 11, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 1, 'type' => CARD_ARTISAN_1, 'subtype' => CARD_TYPE_CUSTOMER,],
        12 => ['result_associative_index' => 12,'card_id' => 12, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 2, 'type' => CARD_ARTISAN_2, 'subtype' => CARD_TYPE_CUSTOMER,],
        13 => ['result_associative_index' => 13,'card_id' => 13, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 1, 'type' => CARD_ARTISAN_3, 'subtype' => CARD_TYPE_CUSTOMER,],
        
        101 => ['result_associative_index' => 101,'card_id' => 101, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 1, 'type' => PATRON_MASTER_ENGINEER, 'subtype' => CARD_TYPE_CLAN_PATRON,],
        102 => ['result_associative_index' => 102,'card_id' => 102, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 1, 'type' => PATRON_TRADER, 'subtype' => CARD_TYPE_CLAN_PATRON,],
        103 => ['result_associative_index' => 103,'card_id' => 103, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 2, 'type' => PATRON_LADY, 'subtype' => CARD_TYPE_CLAN_PATRON,],
        104 => ['result_associative_index' => 104,'card_id' => 104, 'card_location' => CARD_CLAN_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 2, 'type' => PATRON_GOVERNOR, 'subtype' => CARD_TYPE_CLAN_PATRON,],

    ];
    static array $tiles = [

        1 => ['result_associative_index' => 1,'tile_id' => 1, 'card_location' => TILE_LOCATION_MASTERY_CARD, 'tile_state' => 0,  'type' => 1, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
        2 => ['result_associative_index' => 2,'tile_id' => 2, 'card_location' => TILE_LOCATION_MASTERY_CARD, 'tile_state' => 0,  'type' => 2, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
        3 => ['result_associative_index' => 3,'tile_id' => 3, 'card_location' => TILE_LOCATION_MASTERY_CARD, 'tile_state' => 0,  'type' => 3, 'subtype' => TILE_TYPE_MASTERY_CARD, ],
        
        11 => ['result_associative_index' => 11,'tile_id' => 11, 'card_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 1, 'subtype' => TILE_TYPE_SCORING, ],
        12 => ['result_associative_index' => 12,'tile_id' => 12, 'card_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 2, 'subtype' => TILE_TYPE_SCORING, ],
        13 => ['result_associative_index' => 13,'tile_id' => 13, 'card_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 3, 'subtype' => TILE_TYPE_SCORING, ],
        14 => ['result_associative_index' => 14,'tile_id' => 14, 'card_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 4, 'subtype' => TILE_TYPE_SCORING, ],
        15 => ['result_associative_index' => 15,'tile_id' => 15, 'card_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 5, 'subtype' => TILE_TYPE_SCORING, ],
        16 => ['result_associative_index' => 16,'tile_id' => 16, 'card_location' => TILE_LOCATION_SCORING, 'tile_state' => 0,  'type' => 6, 'subtype' => TILE_TYPE_SCORING, ],

        21 => ['result_associative_index' => 21,'tile_id' => 21, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 1, 'subtype' => TILE_TYPE_BUILDING, ],
        22 => ['result_associative_index' => 22,'tile_id' => 22, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 2, 'subtype' => TILE_TYPE_BUILDING, ],
        23 => ['result_associative_index' => 23,'tile_id' => 23, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 3, 'subtype' => TILE_TYPE_BUILDING, ],
        24 => ['result_associative_index' => 24,'tile_id' => 24, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 4, 'subtype' => TILE_TYPE_BUILDING, ],
        25 => ['result_associative_index' => 25,'tile_id' => 25, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 5, 'subtype' => TILE_TYPE_BUILDING, ],
        26 => ['result_associative_index' => 26,'tile_id' => 26, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_1, 'tile_state' => 0,  'type' => 6, 'subtype' => TILE_TYPE_BUILDING, ],

        101 => ['result_associative_index' => 101,'tile_id' => 101, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 11, 'subtype' => TILE_TYPE_BUILDING, ],
        102 => ['result_associative_index' => 102,'tile_id' => 102, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 12, 'subtype' => TILE_TYPE_BUILDING, ],
        103 => ['result_associative_index' => 103,'tile_id' => 103, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 13, 'subtype' => TILE_TYPE_BUILDING, ],
        104 => ['result_associative_index' => 104,'tile_id' => 104, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 14, 'subtype' => TILE_TYPE_BUILDING, ],
        105 => ['result_associative_index' => 105,'tile_id' => 105, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 15, 'subtype' => TILE_TYPE_BUILDING, ],
        106 => ['result_associative_index' => 106,'tile_id' => 106, 'card_location' => TILE_LOCATION_BUILDING_DECK_ERA_2, 'tile_state' => 0,  'type' => 16, 'subtype' => TILE_TYPE_BUILDING, ],
    ];
}
