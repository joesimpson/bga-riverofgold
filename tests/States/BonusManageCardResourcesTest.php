<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use Bga\Games\RiverOfGoldNightMarket\States\BonusManageCardResources;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use ROG\Models\ScenarioType;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

final class BonusManageCardResourcesTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_debt(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        $expectedArgs = [
            'c' => $currentBonus,
            'card_id' => 301,
            'types' => [
                RESOURCE_TYPE_MONEY => [
                    'min' => 0,
                    'max' => 25,
                ],
            ],
            'skip' => false,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_removeGoods(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_REMOVE_GOODS;
        $spaces = [21,22,23,25];
        $currentBonusDatas = ['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,44,45], 'bonusQuantity'=>1, 'resources' => [1,2,3] ];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":1,"3":2,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        $expectedArgs = [
            'c' => $currentBonus,
            'card_id' => 301,
            'meeples' => [43,44,45],
            'spaces' => $spaces,
            'resources' => [RESOURCE_TYPE_SILK,RESOURCE_TYPE_POTTERY,RESOURCE_TYPE_RICE] ,
            'types' => [
                //RESOURCE_TYPE_SILK =>    [ 'min' => 1, 'max' => 1, ],
                RESOURCE_TYPE_POTTERY => [ 'min' => 1, 'max' => 1, ],
                RESOURCE_TYPE_RICE =>    [ 'min' => 1, 'max' => 1, ],
            ],
            'skip' => true,
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
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    
    // -------------------------------------------------

    public function test_ActionSkipBonuses_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You should not skip these bonuses");
        $state->actSkipBonuses(999999, 1, $args);
    }
    public function test_ActionSkipBonuses_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_REMOVE_GOODS;
        $spaces = [21,22,23,25];
        $currentBonusDatas = ['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,45], 'bonusQuantity'=>1, 'resources' => [3] ];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{}'];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."3",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."5",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        $args = $state->getArgs();

        $newState = $state->actSkipBonuses(999999, 1, $args);
        
        $expectedNotifs = [];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(0, Globals::getCurrentBonus());
        assertSame(null, Globals::getCurrentBonusDatas());
        assertSame(ST_BONUS_CHOICE, $newState);
    }

    // -------------------------------------------------
 
    public function test_actManageCardResources_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        TestDatas::$tokens[22]['meeple_location'] = MEEPLE_LOCATION_CARD."301";
        TestDatas::$tokens[22]['meeple_state'] = 1;
        $qty = 14;
        $type = RESOURCE_TYPE_MONEY;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResourceOnCard-1",
            "spendResource-1",
            "addResourceOnCard-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(11, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(19, $resourcesCard[RESOURCE_TYPE_MONEY]);//16 + 3 interest
        //NOT gain ship
        assertSame(MEEPLE_LOCATION_CARD."301", TestDatas::$tokens[22]['meeple_location']);
        assertSame(1, TestDatas::$tokens[22]['meeple_state']);
    }
    
    public function test_actManageCardResources_Pass_GainSecondShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        TestDatas::$tokens[22]['meeple_location'] = MEEPLE_LOCATION_CARD."301";
        TestDatas::$tokens[22]['meeple_state'] = 1;
        $qty = 25;
        $type = RESOURCE_TYPE_MONEY;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResourceOnCard-1",
            "spendResource-1",
            "newBoat-1",
            "addResourceOnCard-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(6, $resourcesCard[RESOURCE_TYPE_MONEY]);//5 + 1 interest
        //gain ship
        assertSame(MEEPLE_LOCATION_RIVER, TestDatas::$tokens[22]['meeple_location']);
        assertSame(8, TestDatas::$tokens[22]['meeple_state']);
    }
    
    public function test_actManageCardResources_Pass_RemoveDebt(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":2}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":1}'];
        //set influence
        TestDatas::$tokens[1]['meeple_state'] = 0 ;// +2 will give 1 pottery *2
        TestDatas::$tokens[2]['meeple_state'] = 3 ;// +2 will give 2 koku *2 (+ 1 rice repeated)
        TestDatas::$tokens[3]['meeple_state'] = 6 ;// +2 will give (2 koku + 1 silk repeated)
        TestDatas::$tokens[4]['meeple_state'] = 8 ;// +2 will give  1 sun *2 (2 koku + 1 pottery repeated)
        TestDatas::$tokens[5]['meeple_state'] = 12;// +2 will give 3 points *2 (1 sun + 2 koku + 1 rice repeated)
        TestDatas::$tokens[6]['meeple_state'] = 15;// +2 will give (3 points + 1 sun + 2 koku + 1 silk repeated)
        $qty = 1;
        $type = RESOURCE_TYPE_MONEY;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResourceOnCard-1",
            "spendResource-1",
            "removeDebt-1",
            "gainInfluence-1",
            "giveResource-1",
            "gainInfluence-1",
            "giveResource-1",
            "gainInfluence-1",
            "gainInfluence-1",
            "giveResource-1",
            "gainInfluence-1",
            "addPoints-1",
            "gainInfluence-1",
            "trackRewards-1",
            "giveResource-1",
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "giveResource-1",
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "giveResource-1",
            "addPoints-1",
            "trackRewards-1",
            "giveResource-1",
            "giveResource-1",
            "giveResource-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(28, TestDatas::$players[1]['player_score']);//19+3*3
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_SILK]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_RICE]);
        assertSame(4, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(4, $resourcesP1[RESOURCE_TYPE_SUN]); 
        assertSame(13, $resourcesP1[RESOURCE_TYPE_MONEY]);//1 + 12
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(0, $resourcesCard[RESOURCE_TYPE_MONEY]);
        //gain influence 
        assertSame(2+0 , TestDatas::$tokens[1]['meeple_state']);
        assertSame(2+3 , TestDatas::$tokens[2]['meeple_state']);
        assertSame(2+6 , TestDatas::$tokens[3]['meeple_state']);
        assertSame(2+8 , TestDatas::$tokens[4]['meeple_state']);
        assertSame(2+12, TestDatas::$tokens[5]['meeple_state']);
        assertSame(2+15, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_actManageCardResources_Pass_Pay0_IncreaseDebt(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        TestDatas::$tokens[22]['meeple_location'] = MEEPLE_LOCATION_CARD."301";
        TestDatas::$tokens[22]['meeple_state'] = 1;
        $qty = 0;
        $type = RESOURCE_TYPE_MONEY;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "addResourceOnCard-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(25, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(36, $resourcesCard[RESOURCE_TYPE_MONEY]);//30 + 6 interest
        //NOT gain ship
        assertSame(MEEPLE_LOCATION_CARD."301", TestDatas::$tokens[22]['meeple_location']);
        assertSame(1, TestDatas::$tokens[22]['meeple_state']);
    }
    
    public function test_actManageCardResources_Pass_RemoveLastResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_REMOVE_GOODS;
        $spaces = [21,22,23,25];
        $currentBonusDatas = ['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,45], 'bonusQuantity'=>1, 'resources' => [3] ];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":2,"3":3,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{}'];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."3",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."5",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        $qty = 1;
        $type = RESOURCE_TYPE_RICE;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResource-1",
            "removeClanMarker-1",//removeResourceMarker
            "removeClanMarker-1",//removeResourceMarker
            "addResourceOnCard-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_BONUS_CHOICE, $newState);
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK]);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_RICE]);//-1
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(2, $resourcesCard[RESOURCE_TYPE_RICE]);
        assertFalse(array_key_exists(RESOURCE_TYPE_SILK,$resourcesCard));
        assertFalse(array_key_exists(RESOURCE_TYPE_POTTERY,$resourcesCard));
        assertFalse(array_key_exists(43,TestDatas::$tokens));
        assertFalse(array_key_exists(45,TestDatas::$tokens));
        assertSame(21, TestDatas::$players[1]['player_score']);//19+2
    }
    
    public function test_actManageCardResources_Pass_RemoveRice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_REMOVE_GOODS;
        $spaces = [21,22,23,25];
        $currentBonusDatas = ['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,44,45], 'bonusQuantity'=>1, 'resources' => [1,3] ];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":2,"3":3,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{}'];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."3",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."4",'type' => MEEPLE_TYPE_RESOURCE_SILK,  'player_id' => null,  ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."5",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        $qty = 1;
        $type = RESOURCE_TYPE_RICE;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResource-1",
            "removeClanMarker-1",//removeResourceMarker
            "removeClanMarker-1",//removeResourceMarker
            "addResourceOnCard-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(BonusManageCardResources::class, $newState);//stay in state to resolve next resource
        assertSame($currentBonus, Globals::getCurrentBonus());
        assertSame(['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[44], 'bonusQuantity'=>1, 'resources' => [1] ], Globals::getCurrentBonusDatas());
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK]);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_RICE]);//-1
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(2, $resourcesCard[RESOURCE_TYPE_RICE]);
        assertFalse(array_key_exists(RESOURCE_TYPE_SILK,$resourcesCard));
        assertFalse(array_key_exists(RESOURCE_TYPE_POTTERY,$resourcesCard));
        assertFalse(array_key_exists(43,TestDatas::$tokens));
        assertTrue(array_key_exists(44,TestDatas::$tokens));
        assertFalse(array_key_exists(45,TestDatas::$tokens));
        assertSame(21, TestDatas::$players[1]['player_score']);//19+2
    }
    
    public function test_actManageCardResources_Pass_RemoveSilk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_REMOVE_GOODS;
        $spaces = [21,22,23,25];
        $currentBonusDatas = ['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,44,45], 'bonusQuantity'=>1, 'resources' => [1,3] ];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":3,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{}'];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."3",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."4",'type' => MEEPLE_TYPE_RESOURCE_SILK,  'player_id' => null,  ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."5",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        $qty = 1;
        $type = RESOURCE_TYPE_SILK;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResource-1",
            "removeClanMarker-1",//removeResourceMarker
            "addResourceOnCard-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(BonusManageCardResources::class, $newState);//stay in state to resolve next resource
        assertSame($currentBonus, Globals::getCurrentBonus());
        assertSame(['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,45], 'bonusQuantity'=>1, 'resources' => [3] ], Globals::getCurrentBonusDatas());
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resourcesP1[RESOURCE_TYPE_SILK]);//-1
        assertSame(2, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_RICE]);
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(1, $resourcesCard[RESOURCE_TYPE_SILK]);
        assertFalse(array_key_exists(RESOURCE_TYPE_RICE,$resourcesCard));
        assertFalse(array_key_exists(RESOURCE_TYPE_POTTERY,$resourcesCard));
        assertTrue(array_key_exists(43,TestDatas::$tokens));
        assertFalse(array_key_exists(44,TestDatas::$tokens));
        assertTrue(array_key_exists(45,TestDatas::$tokens));
        assertSame(20, TestDatas::$players[1]['player_score']);//19+1
    }
    
    public function test_actManageCardResources_Pass_RemovePorcelain(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_REMOVE_GOODS;
        $spaces = [21,22,23,25];
        $currentBonusDatas = ['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,44,45,46], 'bonusQuantity'=>1, 'resources' => [1,2,3] ];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":3,"4":4,"5":0,"6":25}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"1":4,"2":4,"3":4}'];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."3",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."4",'type' => MEEPLE_TYPE_RESOURCE_SILK,  'player_id' => null,  ];
        TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."5",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[46] = ['result_associative_index' => 46, 'meeple_id' => 46, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."7",'type' => MEEPLE_TYPE_RESOURCE_POTTERY,  'player_id' => null,  ];
        $qty = 1;
        $type = RESOURCE_TYPE_POTTERY;
        $args = $state->getArgs();

        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
        $expectedNotifs = [
            "spendResource-1",
            "removeClanMarker-1",//removeResourceMarker
            "addResourceOnCard-1",
            "addPoints-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(BonusManageCardResources::class, $newState);//stay in state to resolve next resource
        assertSame($currentBonus, Globals::getCurrentBonus());
        assertSame(['card_id'=>301,'shoreSpacesIds'=>$spaces, 'meeplesIds'=>[43,44,45], 'bonusQuantity'=>1, 'resources' => [1,3] ], Globals::getCurrentBonusDatas());
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_SILK]);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_POTTERY]);//-1
        assertSame(3, $resourcesP1[RESOURCE_TYPE_RICE]);
        $resourcesCard = json_decode(TestDatas::$cards[301]['resources'], true);
        assertSame(4, $resourcesCard[RESOURCE_TYPE_SILK]);
        assertSame(5, $resourcesCard[RESOURCE_TYPE_POTTERY]);//+1
        assertSame(4, $resourcesCard[RESOURCE_TYPE_RICE]);
        assertTrue(array_key_exists(43,TestDatas::$tokens));
        assertTrue(array_key_exists(44,TestDatas::$tokens));
        assertTrue(array_key_exists(45,TestDatas::$tokens));
        assertFalse(array_key_exists(46,TestDatas::$tokens));
        assertSame(20, TestDatas::$players[1]['player_score']);//19+1
    }
    
    public function test_actManageCardResources_KO_NoMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":0}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        $qty = 1;
        $type = RESOURCE_TYPE_MONEY;
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid quantity $qty > 0");
        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
        
    }
    
    public function test_actManageCardResources_KO_MinMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":0}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        $qty = -10;
        $type = RESOURCE_TYPE_MONEY;
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid quantity $qty < 0");
        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
    }
    
    public function test_actManageCardResources_KO_WrongResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":0}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        $qty = 1;
        $type = RESOURCE_TYPE_SILK;
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid resource $type");
        $newState = $state->actManageCardResources($qty, $type,999999, 1, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
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
        $state = new BonusManageCardResources($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusManageCardResources($game);
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
        $state = new BonusManageCardResources($game);
        $currentBonus = BONUS_TYPE_MANAGE_DEBT;
        $currentBonusDatas = ['card_id'=>301, 'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":30}'];
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}