<?php

declare(strict_types=1);

namespace {

    use Bga\GameFramework\Bga;
    use Bga\GameFramework\Notify;
    //use RiverOfGold;
    use ROG\Core\Globals;

#[\AllowDynamicProperties]
class GameMock extends RiverOfGold {

    public function __construct()
    {
        parent::__construct();
        $this->gamestate = new GamestateMachineMock();
        $this->notify = new Notify($this);
        $this->bga = new Bga($this);
        //DB_Manager::startLog();
        Globals::fetch();
    }

    public function getStatTypes(){
        $stats = json_decode('{
            "table": {
                "turns_number": {
                    "id": 10,
                    "name": "Number of turns",
                    "type": "int"
                }
            },
            "player": {
                "score": {
                "id": 10,
                "name": "Score",
                "type": "int"
                },
                "turnOrder": {
                "id": 15,
                "name": "Turn order",
                "type": "int"
                },
                "endPlayer": {
                "id": 11,
                "name": "Player triggers the end",
                "type": "int"
                },
                "playedClan": {
                "id": 12,
                "name": "Played clan",
                "type": "int"
                },
                "playedClanPatron": {
                "id": 13,
                "name": "Played clan patron",
                "type": "int"
                },
                "nbActionsBuild": {
                "id": 20,
                "name": "Build actions",
                "type": "int"
                },
                "nbActionsSail": {
                "id": 21,
                "name": "Sail actions",
                "type": "int"
                },
                "nbActionsDeliver": {
                "id": 22,
                "name": "Deliver actions",
                "type": "int"
                },
                "moneyReceived": {
                "id": 30,
                "name": "Money received",
                "type": "int"
                },
                "moneySpent": {
                "id": 31,
                "name": "Money spent",
                "type": "int"
                },
                "moneyLeft": {
                "id": 32,
                "name": "Money left",
                "type": "int"
                }
            },
            "value_labels": {
                "11": {
                "0": "",
                "1": "Yes"
                },
                "14": {
                "0": "",
                "1": "Yes"
                },
                "12": {
                "0": "",
                "1": "Crab Clan",
                "2": "Mantis Clan",
                "3": "Crane Clan",
                "4": "Scorpion Clan"
                },
                "13": {
                "0": "",
                "1": "Master Engineer",
                "2": "Wily Trader",
                "3": "Son of Storms",
                "4": "Priestess of Tempests and Tides",
                "5": "The Iron Crane",
                "6": "Darling of the Courts",
                "7": "Governor of the City of lies",
                "8": "Lady of Whispers"
                }
            }
            }', true);
        //$this->dump("StatsTypes : ", $stats);
        return $stats;
    }
}

class GamestateMachineMock extends \Bga\GameFramework\GamestateMachine
{
    public function getCurrentMainStateId(): ?int {
        return ST_PLAYER_TURN;
    }
    
    
}

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
            'resources' => '[]', 
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
            
    ];
    static array $cards = [
        1 => ['result_associative_index' => 1,'card_id' => 1, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 1, 'type' => CARD_ARTISAN_1, 'subtype' => CARD_TYPE_CUSTOMER,],
        2 => ['result_associative_index' => 2,'card_id' => 2, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 2, 'type' => CARD_ARTISAN_2, 'subtype' => CARD_TYPE_CUSTOMER,],
        3 => ['result_associative_index' => 3,'card_id' => 3, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 2, 'type' => CARD_ARTISAN_3, 'subtype' => CARD_TYPE_CUSTOMER,],

    ];
    static array $tiles = [
    ];
}

}