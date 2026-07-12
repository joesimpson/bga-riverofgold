<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusAdvanceCity;
use Bga\Games\RiverOfGoldNightMarket\States\BonusAdvanceCityChoice;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusAdvanceCityTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_ArgsWithCityOfLies_col1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>1, 'p' => true, 'cost' => [], ], 
                    ['space' =>5, 'p' => true, 'cost' => [], ], 
                    ['space' =>9, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_ArgsWithCityOfLies_col2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."1-1";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>2, 'p' => true, 'cost' => [], ], 
                    ['space' =>6, 'p' => true, 'cost' => [], ], 
                    ['space' =>10, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_ArgsWithCityOfLies_col3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."2-1";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>3, 'p' => true, 'cost' => [], ], 
                    ['space' =>7, 'p' => true, 'cost' => [], ], 
                    ['space' =>11, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_ArgsWithCityOfLies_col4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."3-1";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>4, 'p' => true, 'cost' => [], ], 
                    ['space' =>8, 'p' => true, 'cost' => [], ], 
                    ['space' =>12, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_ArgsWithCityOfLies_col4_UsedSpaces(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."3-1";
        TestDatas::$tokens[38]['meeple_location'] = MEEPLE_LOCATION_CITY."4-2";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>4, 'p' => true, 'cost' => [], ], 
                    ['space' =>12, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_ArgsWithCityOfLies_col5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."4-1";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>101, 'p' => true, 'cost' => [], ], 
                    ['space' =>102, 'p' => true, 'cost' => [], ], 
                    ['space' =>103, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_ArgsWithCityOfLies_col5_UsedSpaces(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."4-1";
        TestDatas::$tokens[38]['meeple_location'] = MEEPLE_LOCATION_CITY."5-1";
        TestDatas::$tokens[39]['meeple_location'] = MEEPLE_LOCATION_CITY."5-3";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
                37 => [ 
                    ['space' =>102, 'p' => true, 'cost' => [], ], 
                ], 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_ArgsWithCityOfLies_col6_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setChoices(0);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."5-1";
        $expectedArgs = [
            'c' => BONUS_TYPE_ADVANCE_OR_POINTS,
            'p' => [
                BonusAdvanceCityChoice::ADVANCE->value,
                BonusAdvanceCityChoice::POINTS->value,
            ],
            'citySpaces' => [ 
            ],
            'score' => 5,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_EnteringState_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_actSelectAdvance_Pass_Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        $args = $state->getArgs();
        $choice = BonusAdvanceCityChoice::POINTS->value;

        $newState = $state->actSelectAdvance($choice,999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    public function test_actSelectAdvance_KO_WrongChoice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        $args = $state->getArgs();
        $choice = 99;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid choice $choice");
        $state->actSelectAdvance($choice,999999, 1, $args);
    }
    // -------------------------------------------------

    public function test_actSelectAdvance_Advance_Pass_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        TestDatas::resetCityTokens();
        $args = $state->getArgs();
        $choice = BonusAdvanceCityChoice::ADVANCE->value;
        $space = 1;
        $markerId = 37;

        $newState = $state->actSelectAdvance($choice,999999, 1, $args, $space, $markerId);
        
        $expectedNotifs = [
            "moveCityMarker-1",
            "addBonus-1",
            "addBonus-1",
            "addBonus-1", //city draw
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(MEEPLE_LOCATION_CITY."1-1", TestDatas::$tokens[$markerId]['meeple_location']);
        $expectedBonuses = [
            BONUS_TYPE_CHOICE, BONUS_TYPE_CHOICE,
            'datas' => [
                BONUS_TYPE_CITY_CARD_DRAW => [
                    1 => [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_OUTER_1],
                ],
                
            ],
        ];
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    public function test_actSelectAdvance_Advance_Pass_ToSpace12(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."3-1";
        $args = $state->getArgs();
        $choice = BonusAdvanceCityChoice::ADVANCE->value;
        $space = 12;
        $markerId = 37;

        $newState = $state->actSelectAdvance($choice,999999, 1, $args, $space, $markerId);
        
        $expectedNotifs = [
            "moveCityMarker-1",
            "addBonus-1",
            "addBonus-1", //city draw
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(MEEPLE_LOCATION_CITY."4-3", TestDatas::$tokens[$markerId]['meeple_location']);
        $expectedBonuses = [
            'datas' => [
                BONUS_TYPE_FREE_SAIL => [
                    1 => ['bonusQuantity'=>1],
                ],
                BONUS_TYPE_CITY_CARD_DRAW => [
                    1 => [ 'bonusQuantity'=>1, 'location' => CARD_CITY_LOCATION_INNER_2],
                ],
            ],
        ];
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    public function test_actSelectAdvance_Advance_Pass_ToTileSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        TestDatas::resetCityTokens();
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."4-3";
        $args = $state->getArgs();
        $choice = BonusAdvanceCityChoice::ADVANCE->value;
        $space = 101;
        $markerId = 37;

        $newState = $state->actSelectAdvance($choice,999999, 1, $args, $space, $markerId);
        
        $expectedNotifs = [
            "moveCityMarker-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(MEEPLE_LOCATION_CITY."5-1", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(21, TestDatas::$players[1]['player_score']);//+2
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    public function test_actSelectAdvance_Advance_KO_WrongMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        TestDatas::resetCityTokens();
        $args = $state->getArgs();
        $choice = BonusAdvanceCityChoice::ADVANCE->value;
        $space = 1;
        $markerId = 25;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid marker $markerId");
        $newState = $state->actSelectAdvance($choice,999999, 1, $args, $space, $markerId);
    }

    public function test_actSelectAdvance_Advance_KO_WrongSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        Globals::setCurrentBonus(BONUS_TYPE_ADVANCE_OR_POINTS);
        TestDatas::resetCityTokens();
        $args = $state->getArgs();
        $choice = BonusAdvanceCityChoice::ADVANCE->value;
        $space = 2;
        $markerId = 37;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid space $space");
        $newState = $state->actSelectAdvance($choice,999999, 1, $args, $space, $markerId);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
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
        $state = new BonusAdvanceCity($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusAdvanceCity($game);
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
        $state = new BonusAdvanceCity($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}