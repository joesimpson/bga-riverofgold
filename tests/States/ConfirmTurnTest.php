<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\ClientAnswer;
use ROG\Models\BEFORE_ACTION;
use ROG\Models\CITY_CARD_TYPE;
use ROG\Models\ScenarioType;
use ROG\Models\TURN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class ConfirmTurnTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    public function test_Args_NoUndo(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $choices = 0;
        Globals::setChoices($choices);
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        $expectedArgs = [
            'c' => 1,
            'trade' => false,
            'previousSteps' => [],
            'previousChoices' => $choices,
        ];

        $args = $game->argsConfirmTurn();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_ChoicesToUndo(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $choices = 2;
        Globals::setChoices($choices);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        $expectedArgs = [
            'c' => 1,
            'trade' => false,
            'previousSteps' => [ 1 ],
            'previousChoices' => $choices,
        ];

        $args = $game->argsConfirmTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_ScenarioPhoenix1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $choices = 2;
        Globals::setChoices($choices);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => false];
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":2,"4":4,"5":3,"6":0}';
        $expectedArgs = [
            'c' => 1,
            'trade' => true,
            'a' => [ 'actPlayCard' ],
            'p_cards' => [
                301 => [   
                    'marker' => null,
                    'actions' => [
                        TURN_ACTION::DIVINE_CYCLING->value => [
                        ],
                    ] 
                ],
            ],
            'previousSteps' => [ 1 ],
            'previousChoices' => $choices,
        ];

        $args = $game->argsConfirmTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_CityCard_TravelTroupe(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::TRAVEL_TRO->value;
        $expectedArgs = [
            'c' => 1,
            'trade' => false,
            'a' => [ 'actPlayCard' ],
            '_private' => [
                1 => [
                    'p_cards' => [
                        221 => [ 
                            'actions' => [
                                BEFORE_ACTION::GAIN_INFLUENCE->value => [
                                    'n' => 1,
                                    'regions' => [ 1,2,3,4,5,6 ],
                                ],
                            ],
                            'marker' => null,
                        ],
                    ]
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argsConfirmTurn();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
    
    public function test_EnteringState_AutoConfirm(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        Globals::setChoices(0);

        $game->stConfirmTurn();
        
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    public function test_EnteringState_WaitForAction(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        Globals::setChoices(1);

        $game->stConfirmTurn();
        
        assertSame(ST_CONFIRM_TURN, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
    
    public function test_ActionConfirmTurn_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;

        $game->actConfirmTurn(999999);
        
        assertSame(ST_END_TURN, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $game->actRestart(999999);
        
        //TODO Which state is expected here ?
        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
    }
    public function test_ActionRestart_KO_NoChoices(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        Globals::setChoices(0);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("No choice to undo. You may need to reload the page.");
        $game->actRestart(999999);
    }
    // -------------------------------------------------
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        Globals::setChoices(1);
        $stepId = 1;
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $game->actUndoToStep($stepId,999999);
        
        //TODO Which state is expected here ?
        assertSame(ST_GAME_SETUP, GamestateMachine::$test_current_state);
    }
    public function test_ActionUndo_KO_WrongStep(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        Globals::setChoices(0);
        $stepId = 19;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("This step is not undoable anymore. You may need to reload the page.");
        $game->actUndoToStep($stepId,999999);
    }
    // -------------------------------------------------
    // actPlayCard defined in PlayerTurnTrait
    // -------------------------------------------------
    
    public function test_ActionPlayCard_GainInfluenceBeforeAction_Pass_City_TravelTroupe_GainBonus(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_CONFIRM_TURN;
        $cardId = 221;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[$cardId]['player_id'] = 1;
        TestDatas::$cards[$cardId]['type'] = CITY_CARD_TYPE::TRAVEL_TRO->value;
        TestDatas::$players[1]['bonuses'] = json_encode([]);
        $markerId = null;
        $action = BEFORE_ACTION::GAIN_INFLUENCE->value;
        $source = null;
        $dest = 1;//region
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );
        TestDatas::$tokens[$dest]['meeple_state'] = 17;

        $game->actPlayCard($answer,999999);
        
        $expectedNotifs = [
            "revealCityCard-1",
            "gainInfluence-1",
            "addBonus-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //Test moved card: 
        assertSame(CARD_CITY_LOCATION_REVEALED, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1,  TestDatas::$cards[$cardId]['card_played']);
        //Test gained influence :
        assertSame(18, TestDatas::$tokens[1]['meeple_state']);//+1
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        // bonuses :
        assertSame(json_encode([BONUS_TYPE_CHOICE]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(ST_CONFIRM_TURN, Globals::getStateBeforeBonus());
    }
    // -------------------------------------------------
}