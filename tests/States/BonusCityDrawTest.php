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
                            'state' => 1,
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
                            'state' => 3,
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
        
        assertSame(null, $newState);
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

        $newState = $state->actTakeCityCard($cardId, 999999, 1, $args);
        
        $expectedNotifs = [
            "giveCityCardTo-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(CARD_CITY_LOCATION_HAND, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
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

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $newState = $state->actTakeCityCard($cardId, 999999, 1, $args);
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