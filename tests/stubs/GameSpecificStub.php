<?php

declare(strict_types=1);

namespace {

    use Bga\GameFramework\Bga;
    use Bga\GameFramework\Notify;
    //use RiverOfGold;
    use ROG\Core\Globals;
    use Tests\Utils\TestDatas;

#[\AllowDynamicProperties]
class GameMock extends RiverOfGold {

    public function __construct()
    {
        parent::__construct();
        $this->gamestate = new GamestateMachineMock();
        $this->notify = new Notify($this);
        $this->bga = new Bga($this);
        $this->player_preferences =[];
        //DB_Manager::startLog();
        Globals::fetch();
        
        TestDatas::$test_activePlayerId = 1;
        Globals::setTurnPlayer(TestDatas::$test_activePlayerId);
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
        return self::$test_current_state;
    }
}

}