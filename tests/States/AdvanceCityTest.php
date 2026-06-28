<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\AdvanceCity;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Models\MAIN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

final class AdvanceCityTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_Region1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$players[1]['die_face'] = 1;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>1, 'p' => true, 'cost' => [1], ], 
                    ['space' =>3, 'p' => true, 'cost' => [1,1,2], ], 
                    ['space' =>101, 'p' => true, 'cost' => [1,1,2,2,3], ], 
                    ['space' =>102, 'p' => true, 'cost' => [1,1,2,2,3], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$players[1]['die_face'] = 2;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>2, 'p' => true, 'cost' => [1,1], ], 
                    ['space' =>4, 'p' => true, 'cost' => [1,1,2,2], ], 
                    ['space' =>103, 'p' => true, 'cost' => [1,1,2,2,3], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$players[1]['die_face'] = 3;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>5, 'p' => true, 'cost' => [1], ], 
                    ['space' =>7, 'p' => true, 'cost' => [1,1,2], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$players[1]['die_face'] = 4;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>6, 'p' => true, 'cost' => [1,1], ], 
                    ['space' =>8, 'p' => true, 'cost' => [1,1,2,2,], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 5;
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>9, 'p' => true, 'cost' => [1,], ], 
                    ['space' =>11, 'p' => true, 'cost' => [1,1,2,], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region5_EnoughInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 5;
        TestDatas::$tokens[6]['meeple_state'] = 4;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>9, 'p' => true, 'cost' => [1,], ], 
                    ['space' =>11, 'p' => true, 'cost' => [1,1,2,], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Region6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 6;
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>10, 'p' => true, 'cost' => [1,1], ], 
                    ['space' =>12, 'p' => true, 'cost' => [1,1,2,2,], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Region6_LowInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        TestDatas::$players[1]['die_face'] = 6;
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 1;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>10, 'p' => true, 'cost' => [1,1], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_CannotGoBackward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$tokens[37]['meeple_location'] = MEEPLE_LOCATION_CITY."3-1";
        TestDatas::$players[1]['die_face'] = 1;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                   1=> ['space' =>101, 'p' => true, 'cost' => [2,3], ], 
                   2=> ['space' =>102, 'p' => true, 'cost' => [2,3], ], 
                ], 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_UsedSpaces(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$tokens[38]['meeple_location'] = MEEPLE_LOCATION_CITY."1-1";
        TestDatas::$players[1]['die_face'] = 1;
        $expectedArgs = [
            'citySpaces' => [ 37 => [ 
                    ['space' =>3,   'p' => true, 'cost' => [1,1,2], ], 
                    ['space' =>101, 'p' => true, 'cost' => [1,1,2,2,3], ], 
                    ['space' =>102, 'p' => true, 'cost' => [1,1,2,2,3], ], 
                ], 
            ],
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
        $state = new AdvanceCity($game);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_actSelectAdvanceDest_Pass_space1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;
        $selectedRegionsToPay = [1, ];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
            "addBonus-1",
            "addBonus-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."1-1", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);//-1
        assertSame(3, TestDatas::$tokens[2]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[3]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        assertSame(json_encode([BONUS_TYPE_CHOICE,BONUS_TYPE_CHOICE ]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    public function test_actSelectAdvanceDest_Pass_space2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":3,"6":0}';
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 2;
        $markerId = 37;
        $selectedRegionsToPay = [1,3 ];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
            "addBonus-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."2-1", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);//-1
        assertSame(3, TestDatas::$tokens[2]['meeple_state']);//-0
        assertSame(2, TestDatas::$tokens[3]['meeple_state']);//-1
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(19, TestDatas::$players[1]['player_score']);
        assertSame(json_encode([BONUS_TYPE_SECOND_MARKER_ON_BUILDING]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    public function test_actSelectAdvanceDest_Pass_space5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::$players[1]['die_face'] = 3;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":3,"6":0}';
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 5;
        $markerId = 37;
        $selectedRegionsToPay = [1, ];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
            "giveResource-1",
            "giveResource-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."1-2", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(2, TestDatas::$tokens[1]['meeple_state']);//-1
        assertSame(3, TestDatas::$tokens[2]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[3]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//+1
        assertSame(4, $resources[RESOURCE_TYPE_SUN]);//+1
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    public function test_actSelectAdvanceDest_Pass_space6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":3,"6":0}';
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 6;
        $markerId = 37;
        $selectedRegionsToPay = [2,3 ];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
            "giveCardToPublic-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."2-2", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(3, TestDatas::$tokens[1]['meeple_state']);//-0
        assertSame(2, TestDatas::$tokens[2]['meeple_state']);//-1
        assertSame(2, TestDatas::$tokens[3]['meeple_state']);//-1
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        //Check draw 1 card in hand :
        assertSame(1, TestDatas::$cards[1]['player_id']);
        assertSame(CARD_LOCATION_HAND, TestDatas::$cards[1]['card_location']);
        assertSame(3, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    
    public function test_actSelectAdvanceDest_Pass_space9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::$players[1]['die_face'] = 5;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":3,"6":0}';
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 9;
        $markerId = 37;
        $selectedRegionsToPay = [4, ];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
            "giveResource-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."1-3", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(3, TestDatas::$tokens[1]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[2]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[3]['meeple_state']);//-0
        assertSame(2, TestDatas::$tokens[4]['meeple_state']);//-1
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);
        assertSame(7, $resources[RESOURCE_TYPE_MONEY]);//+7
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    
    public function test_actSelectAdvanceDest_Pass_space10(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::$players[1]['die_face'] = 6;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":3,"6":0}';
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 10;
        $markerId = 37;
        $selectedRegionsToPay = [6, 6,];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."2-3", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(3, TestDatas::$tokens[1]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[2]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[3]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(1, TestDatas::$tokens[6]['meeple_state']);//-2
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    public function test_actSelectAdvanceDest_Pass_ToTile1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 101;
        $markerId = 37;
        $selectedRegionsToPay = [1,1,2,3,4 ];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."5-1", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);//-1*2
        assertSame(1, TestDatas::$tokens[2]['meeple_state']);//-2
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);//-2
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);//-3
        assertSame(3, TestDatas::$tokens[5]['meeple_state']);//-0
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    public function test_actSelectAdvanceDest_Pass_ToTile2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<=6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 102;
        $markerId = 37;
        $selectedRegionsToPay = [1,1,2,3,5];

        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
        
        $expectedNotifs = [
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "gainInfluence-1",//spendInfluence
            "moveCityMarker-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsAdvance']);
        assertSame(MEEPLE_LOCATION_CITY."5-2", TestDatas::$tokens[$markerId]['meeple_location']);
        assertSame(MAIN_ACTION::ADVANCE->value, Globals::getTurnMainActionDone());
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);//-1*2
        assertSame(1, TestDatas::$tokens[2]['meeple_state']);//-2
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);//-2
        assertSame(3, TestDatas::$tokens[4]['meeple_state']);//-0
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);//-3
        assertSame(3, TestDatas::$tokens[6]['meeple_state']);//-0
        assertSame(ST_CONFIRM_CHOICES, $newState);
    }
    public function test_actSelectAdvanceDest_KO_WrongSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 99;
        $markerId = 37;
        $selectedRegionsToPay = [1,1,2,3,4 ];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid space $space");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    
    public function test_actSelectAdvanceDest_KO_UsedSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$tokens[38]['meeple_location'] = MEEPLE_LOCATION_CITY."1-1";
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;
        $selectedRegionsToPay = [1, ];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid space $space");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    public function test_actSelectAdvanceDest_KO_WrongPlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;
        $selectedRegionsToPay = [1, ];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid marker 37");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    
    public function test_actSelectAdvanceDest_KO_NotEnoughCostsToPay(): void
    {
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;
        $selectedRegionsToPay = [ ];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid number of influence to pay lanterns");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    public function test_actSelectAdvanceDest_KO_TooManyCostsToPay(): void
    {
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<6;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;
        $selectedRegionsToPay = [3,3,6 ];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid number of influence to pay lanterns");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    public function test_actSelectAdvanceDest_KO_WrongRegionToPay1(): void
    {
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<5;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$tokens[6]['meeple_state'] = 0;
        $args = $state->getArgs();
        $space = 1;
        $markerId = 37;
        $selectedRegionsToPay = [ 6];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Player 1 cannot pay 1 influence from region 6");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    public function test_actSelectAdvanceDest_KO_WrongRegionToPay3(): void
    {
        $game = new GameMock();
        $state = new AdvanceCity($game);
        TestDatas::resetCityTokens();
        for($k=1;$k<5;$k++ ) TestDatas::$tokens[$k]['meeple_state'] = 3;
        TestDatas::$tokens[6]['meeple_state'] = 2;
        $args = $state->getArgs();
        $space = 101;
        $markerId = 37;
        $selectedRegionsToPay = [1,1,2,3, 6];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Player 1 cannot pay 3 influence from region 6");
        $newState = $state->actSelectAdvanceDest($space,$markerId,$selectedRegionsToPay,999999, 1, $args);
    }
    // -------------------------------------------------
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        Globals::setChoices(1);
        TestDatas::$logs[1] = ['result_associative_index' => 1,'id' => 1, 'move_id' => 1, 'table' => '', 'primary'=>'', 'type' => 'step', 'affected' => '[{"name": "currentBonus","value": "24"}]', ];

        $state->actRestart(999999,);
        
        assertSame(1, 1);
    }
    // -------------------------------------------------
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
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
        $state = new AdvanceCity($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
 
    public function test_canPayLanterns_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 3;
        TestDatas::$tokens[2]['meeple_state'] = 3;
        TestDatas::$tokens[3]['meeple_state'] = 3;
        TestDatas::$tokens[4]['meeple_state'] = 3;
        TestDatas::$tokens[5]['meeple_state'] = 3;
        TestDatas::$tokens[6]['meeple_state'] = 3;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_KO_0Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        $influences = $player->getAllInfluences();  

        assertFalse( $state->canPayLanterns($influences, 0,1));
        assertFalse( $state->canPayLanterns($influences, 0,2));
        assertFalse( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertFalse( $state->canPayLanterns($influences, 1,2));
        assertFalse( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertFalse( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertFalse( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_SomeKO_1Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertFalse( $state->canPayLanterns($influences, 0,2));
        assertFalse( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertFalse( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertFalse( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertFalse( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_SomeKO_2Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 2;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertFalse( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertFalse( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_SomeKO_3Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 3;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertFalse( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_3Influence_BadDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 0;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 0;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertFalse( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertFalse( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertFalse( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertFalse( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_SomeKO_4Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 4;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_KO_4Influence_BadDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 0;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertFalse( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertFalse( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertFalse( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertFalse( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_KO_4Influence_BadDistribution2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 2;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 0;
        TestDatas::$tokens[6]['meeple_state'] = 0;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertFalse( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertFalse( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_SomeKO_5Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 5;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertFalse( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_SomeKO_6Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 6;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_SomeKO_7Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 7;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_SomeKO_8Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 8;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_Pass_9Influence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 9;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_9Influence_GoodDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 3;
        TestDatas::$tokens[2]['meeple_state'] = 3;
        TestDatas::$tokens[3]['meeple_state'] = 0;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 0;
        TestDatas::$tokens[6]['meeple_state'] = 3;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_9Influence_GoodDistribution2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 8;
        TestDatas::$tokens[2]['meeple_state'] = 0;
        TestDatas::$tokens[3]['meeple_state'] = 0;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 0;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_9Influence_GoodDistribution3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 0;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 2;
        TestDatas::$tokens[5]['meeple_state'] = 5;
        TestDatas::$tokens[6]['meeple_state'] = 0;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_9Influence_BadDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 3;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 3;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_9Influence_BadDistribution2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 4;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_Pass_9Influence_BadDistribution3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 2;
        TestDatas::$tokens[2]['meeple_state'] = 2;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 2;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }

    public function test_canPayLanterns_Pass_10Influence_GoodDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 6;
        TestDatas::$tokens[2]['meeple_state'] = 0;
        TestDatas::$tokens[3]['meeple_state'] = 0;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 2;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_Pass_10Influence_GoodDistribution2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 0;
        TestDatas::$tokens[2]['meeple_state'] = 0;
        TestDatas::$tokens[3]['meeple_state'] = 10;
        TestDatas::$tokens[4]['meeple_state'] = 0;
        TestDatas::$tokens[5]['meeple_state'] = 0;
        TestDatas::$tokens[6]['meeple_state'] = 0;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    public function test_canPayLanterns_Pass_10Influence_BadDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 2;
        TestDatas::$tokens[2]['meeple_state'] = 2;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 2;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }

    public function test_canPayLanterns_Pass_11Influence_BadDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 6;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 1;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_11Influence_GoodDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 2;
        TestDatas::$tokens[5]['meeple_state'] = 4;
        TestDatas::$tokens[6]['meeple_state'] = 2;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_12Influence_GoodDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 2;
        TestDatas::$tokens[2]['meeple_state'] = 2;
        TestDatas::$tokens[3]['meeple_state'] = 3;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 2;
        TestDatas::$tokens[6]['meeple_state'] = 2;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_12Influence_BadDistribution1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 2;
        TestDatas::$tokens[2]['meeple_state'] = 2;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[4]['meeple_state'] = 2;
        TestDatas::$tokens[5]['meeple_state'] = 2;
        TestDatas::$tokens[6]['meeple_state'] = 2;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertFalse( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertFalse( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertFalse( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertFalse( $state->canPayLanterns($influences, 3,5));
        assertFalse( $state->canPayLanterns($influences, 4,5));
    }
    
    public function test_canPayLanterns_Pass_13Influence_GoodDistribution(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new AdvanceCity($game);
        $player = Players::get(1);
        TestDatas::$tokens[1]['meeple_state'] = 2;
        TestDatas::$tokens[2]['meeple_state'] = 2;
        TestDatas::$tokens[3]['meeple_state'] = 3;
        TestDatas::$tokens[4]['meeple_state'] = 2;
        TestDatas::$tokens[5]['meeple_state'] = 2;
        TestDatas::$tokens[6]['meeple_state'] = 2;
        $influences = $player->getAllInfluences();  

        assertTrue( $state->canPayLanterns($influences, 0,1));
        assertTrue( $state->canPayLanterns($influences, 0,2));
        assertTrue( $state->canPayLanterns($influences, 0,3));
        assertTrue( $state->canPayLanterns($influences, 0,4));
        assertTrue( $state->canPayLanterns($influences, 0,5));
        assertTrue( $state->canPayLanterns($influences, 1,2));
        assertTrue( $state->canPayLanterns($influences, 1,3));
        assertTrue( $state->canPayLanterns($influences, 1,4));
        assertTrue( $state->canPayLanterns($influences, 1,5));
        assertTrue( $state->canPayLanterns($influences, 2,3));
        assertTrue( $state->canPayLanterns($influences, 2,4));
        assertTrue( $state->canPayLanterns($influences, 2,5));
        assertTrue( $state->canPayLanterns($influences, 3,4));
        assertTrue( $state->canPayLanterns($influences, 3,5));
        assertTrue( $state->canPayLanterns($influences, 4,5));
    }
    // -------------------------------------------------
}