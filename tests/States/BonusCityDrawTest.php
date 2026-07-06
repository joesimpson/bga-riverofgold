<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusCityDraw;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Helpers\Collection;
use ROG\Models\CITY_CARD_EFFECT;
use ROG\Models\CITY_CARD_TYPE;
use ROG\Models\MAIN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusCityDrawTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            '_private' => [ 
                1 => [
                    'cards' => [ 
                        221 => [
                            'id' => 221,
                            'location' => CARD_CITY_LOCATION_OUTER_1,
                            'pId' => null,
                            'type' => CITY_CARD_TYPE::BRIBERY->value,
                            'inner' => false,
                            'effect' => CITY_CARD_EFFECT::REVEAL->value,
                            'title' => 'Bribery',
                            'subtype' => CARD_TYPE_CITY,
                            'state' => 3,
                            'pos_clan' => [],
                        ],
                        222 => [
                            'id' => 222,
                            'location' => CARD_CITY_LOCATION_OUTER_1,
                            'pId' => null,
                            'type' => CITY_CARD_TYPE::BLACK_MARKET->value,
                            'inner' => false,
                            'effect' => CITY_CARD_EFFECT::REVEAL->value,
                            'title' => 'Black Market',
                            'subtype' => CARD_TYPE_CITY,
                            'state' => 2,
                            'pos_clan' => [],
                        ],
                        223 => [
                            'id' => 223,
                            'location' => CARD_CITY_LOCATION_OUTER_1,
                            'pId' => null,
                            'type' => CITY_CARD_TYPE::SHARED_CLI->value,
                            'inner' => false,
                            'effect' => CITY_CARD_EFFECT::PREDICT->value,
                            'title' => 'Shared Clients',
                            'subtype' => CARD_TYPE_CITY,
                            'state' => 1,
                            'pos_clan' => [
                                //opponents ids
                                2,
                            ],
                        ],

                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertEquals($expectedArgs, $args);
    }
     public function test_Args_WithSeishin(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        $expectedArgs = [
            'c' => $currentBonus,
            'cbd' => $currentBonusDatas,
            '_private' => [ 
                1 => [
                    'cards' => [ 
                        221 => [
                            'id' => 221,
                            'location' => CARD_CITY_LOCATION_OUTER_1,
                            'pId' => null,
                            'type' => CITY_CARD_TYPE::BRIBERY->value,
                            'inner' => false,
                            'effect' => CITY_CARD_EFFECT::REVEAL->value,
                            'title' => 'Bribery',
                            'subtype' => CARD_TYPE_CITY,
                            'state' => 3,
                            'pos_clan' => [],
                        ],
                        222 => [
                            'id' => 222,
                            'location' => CARD_CITY_LOCATION_OUTER_1,
                            'pId' => null,
                            'type' => CITY_CARD_TYPE::BLACK_MARKET->value,
                            'inner' => false,
                            'effect' => CITY_CARD_EFFECT::REVEAL->value,
                            'title' => 'Black Market',
                            'subtype' => CARD_TYPE_CITY,
                            'state' => 2,
                            'pos_clan' => [],
                        ],
                        223 => [
                            'id' => 223,
                            'location' => CARD_CITY_LOCATION_OUTER_1,
                            'pId' => null,
                            'type' => CITY_CARD_TYPE::SHARED_CLI->value,
                            'inner' => false,
                            'effect' => CITY_CARD_EFFECT::PREDICT->value,
                            'title' => 'Shared Clients',
                            'subtype' => CARD_TYPE_CITY,
                            'state' => 1,
                            'pos_clan' => [
                                //opponents ids
                                2, AUTOMA_PLAYER_ID
                            ],
                        ],

                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertEquals($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_EnteringState_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        $expectedNotifs = [
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(null, $newState);
    }
    public function test_EnteringState_Pass_0PossibleCard_AutoSkip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[222]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[223]['card_location'] = CARD_CITY_LOCATION_HAND;
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        $expectedNotifs = [
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    public function test_EnteringState_Pass_1PossibleCard_AutoChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[223]['card_location'] = CARD_CITY_LOCATION_HAND;
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    public function test_EnteringState_Pass_1PossibleCardPredict_AutoChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[222]['card_location'] = CARD_CITY_LOCATION_HAND;
        $args = $state->getArgs();
        $markerPid = 2;
        $cardId = 223;

        $newState = $state->onEnteringState(1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
            "newClanMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        //New Marker on card
        assertSame(43, TestDatas::$lastInsertedId);
        $newClanMarker = TestDatas::$tokens[43];
        //assertSame(MEEPLE_LOCATION_CARD."$cardId", $newClanMarker['meeple_location']);
        assertSame(MEEPLE_LOCATION_HIDDEN_CARD."1-".CARD_TYPE_CITY."-1-city_h", $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame($markerPid, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    public function test_EnteringState_Pass_1PossibleCardPredict_NoAutoChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[222]['card_location'] = CARD_CITY_LOCATION_HAND;
        $args = $state->getArgs();
        $cardId = 223;

        $newState = $state->onEnteringState(1, $args);
        
        $expectedNotifs = [
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(null, $newState);
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[$cardId]['card_location']);
        assertSame(null, TestDatas::$cards[$cardId]['player_id']);
        //no New Marker on card
        assertSame(1, TestDatas::$lastInsertedId);
    }
    // -------------------------------------------------
 
    public function test_actTakeCityCard_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $cardId = 221;
        $markerPid = null;

        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        assertSame(1, TestDatas::$cards[$cardId]['card_state']);
        //other cards unchanged
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[222]['card_location']);
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[223]['card_location']);
        assertSame(2, TestDatas::$cards[222]['card_state']);
        assertSame(1, TestDatas::$cards[223]['card_state']);
        //No New Marker
        assertSame(1, TestDatas::$lastInsertedId);
    }
    
    public function test_actTakeCityCard_Pass_SecondCard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        $args = $state->getArgs();
        $cardId = 222;
        $markerPid = null;

        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        assertSame(2, TestDatas::$cards[$cardId]['card_state']);//+1
        //other cards unchanged
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[221]['card_location']);
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[223]['card_location']);
        assertSame(1, TestDatas::$cards[221]['card_state']);
        assertSame(1, TestDatas::$cards[223]['card_state']);
        //No New Marker
        assertSame(1, TestDatas::$lastInsertedId);
    }
    
    public function test_actTakeCityCard_Pass_SecondCard_WithOpponentMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        $args = $state->getArgs();
        $cardId = 223;
        $markerPid = 2;

        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
            //"newClanMarker-$markerPid",
            //"cityCardPredict-1",
            "newClanMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        assertSame(2, TestDatas::$cards[$cardId]['card_state']);//+1
        //other cards unchanged
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[221]['card_location']);
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[222]['card_location']);
        assertSame(1, TestDatas::$cards[221]['card_state']);
        assertSame(2, TestDatas::$cards[222]['card_state']);
        //New Marker on card
        assertSame(43, TestDatas::$lastInsertedId);
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_HIDDEN_CARD."1-".CARD_TYPE_CITY."-2-city_h", $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame($markerPid, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    public function test_actTakeCityCard_Pass_WithOpponentMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $cardId = 223;
        $markerPid = 2;

        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
            //"newClanMarker-$markerPid",
            //"cityCardPredict-1",
            "newClanMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        //other cards unchanged
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[221]['card_location']);
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[222]['card_location']);
        assertSame(3, TestDatas::$cards[221]['card_state']);
        assertSame(2, TestDatas::$cards[222]['card_state']);
        //New Marker on card
        assertSame(43, TestDatas::$lastInsertedId);
        $newClanMarker = TestDatas::$tokens[43];
        //assertSame(MEEPLE_LOCATION_CARD."$cardId", $newClanMarker['meeple_location']);
        assertSame(MEEPLE_LOCATION_HIDDEN_CARD."1-".CARD_TYPE_CITY."-1-city_h", $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame($markerPid, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    
    public function test_actTakeCityCard_Pass_WithSeishinMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        $args = $state->getArgs();
        $cardId = 223;
        $markerPid = AUTOMA_PLAYER_ID;

        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
            //"newClanMarker-$markerPid",
            //"cityCardPredict-1",
            "newClanMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        //other cards unchanged
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[221]['card_location']);
        assertSame(CARD_CITY_LOCATION_OUTER_1, TestDatas::$cards[222]['card_location']);
        assertSame(3, TestDatas::$cards[221]['card_state']);
        assertSame(2, TestDatas::$cards[222]['card_state']);
        //New Marker on card
        assertSame(43, TestDatas::$lastInsertedId);
        $newClanMarker = TestDatas::$tokens[43];
        //assertSame(MEEPLE_LOCATION_CARD."$cardId", $newClanMarker['meeple_location']);
        assertSame(MEEPLE_LOCATION_HIDDEN_CARD."1-".CARD_TYPE_CITY."-1-city_h", $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame($markerPid, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }

    public function test_actTakeCityCard_KO_WrongCard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_INNER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $cardId = 221;
        $markerPid = null;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
    } 
    
    public function test_actTakeCityCard_KO_NoMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $cardId = 221;
        $markerPid = 2;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId cannot receive a clan marker");
        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
    } 
    public function test_actTakeCityCard_KO_MissingMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $cardId = 223;
        $markerPid = null;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId must receive a clan marker");
        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
    } 
    public function test_actTakeCityCard_KO_WrongMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_CITY_CARD_DRAW;
        $currentBonusDatas = [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();
        $cardId = 223;
        $markerPid = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId must receive a clan marker from ");
        $newState = $state->actTakeCityCard($cardId, $markerPid, 999999, 1, $args);
    } 
    // -------------------------------------------------
 
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $state->actRestart(999999,);
        
        assertSame(1, 1);
    }
    // -------------------------------------------------
 
    public function test_ActionUndo_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $state->actUndoToStep(1, 999999,);
        
        assertSame(1, 1);
    }
    
    // -------------------------------------------------
 
    public function test_Zombie_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusCityDraw($game);
        $currentBonus = BONUS_TYPE_FREE_SAIL;
        $currentBonusDatas = [ 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}