<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\Games\RiverOfGoldNightMarket\States\BonusMultiTrades;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusMultiTradesTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_MultiTrade2Points_0remaining(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>0,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $expectedArgs = [
            'skip' => true,
            'c' => $currentBonus,
            'nb' => 0,
            'trades' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_MultiTrade2Points_0possibles(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>4,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['player_score'] = 1;//not enough score to trade
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":1,"6":0}';
        $expectedArgs = [
            'skip' => true,
            'c' => $currentBonus,
            'nb' => 4,
            'trades' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_MultiTrade2Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>4,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $expectedArgs = [
            'skip' => true,
            'c' => $currentBonus,
            'nb' => 4,
            //'trades' => [
            //    BONUS_TYPE_POINTS =>  [ 'amount' => 2, ] ,
            //    BONUS_TYPE_CHOICE =>  [ 'amount' => 1,
            //        'selection' => [
            //            RESOURCE_TYPE_SILK,
            //            RESOURCE_TYPE_RICE,
            //            RESOURCE_TYPE_POTTERY, 
            //        ],
            //    ],
            //    RESOURCE_TYPE_MONEY => [ 'amount' => 3, ] ,
            //],
            
            'trades' => [
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_SILK     ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_RICE     ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_POTTERY  ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_MONEY    ,'amount' => 3 ]],
                
                ['src' => ['type' => RESOURCE_TYPE_SILK ,'amount' =>1 ],    'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
                ['src' => ['type' => RESOURCE_TYPE_SILK ,'amount' =>1 ],    'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_RICE ,'amount' =>1 ],    'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
                ['src' => ['type' => RESOURCE_TYPE_RICE ,'amount' =>1 ],    'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_POTTERY ,'amount' =>1 ], 'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
                ['src' => ['type' => RESOURCE_TYPE_POTTERY ,'amount' =>1 ], 'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_SILK   ,'amount' =>1 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_RICE   ,'amount' =>1 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_POTTERY,'amount' =>1 ]],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_MultiTrade3Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_MULTITRADE_3;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>3,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $expectedArgs = [
            'skip' => true,
            'c' => $currentBonus,
            'nb' => 1,
            //'trades' => [
            //    BONUS_TYPE_POINTS =>  [ 'amount' => 3, ] ,
            //    BONUS_TYPE_CHOICE =>  [ 'amount' => 1,
            //        'selection' => [
            //            RESOURCE_TYPE_SILK,
            //            RESOURCE_TYPE_RICE,
            //            RESOURCE_TYPE_POTTERY, 
            //        ],
            //    ],
            //    RESOURCE_TYPE_MONEY => [ 'amount' => 3, ] ,
            //],

            'trades' => [
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 3 ], 'dest' => ['type' => RESOURCE_TYPE_SILK     ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 3 ], 'dest' => ['type' => RESOURCE_TYPE_RICE     ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 3 ], 'dest' => ['type' => RESOURCE_TYPE_POTTERY  ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 3 ], 'dest' => ['type' => RESOURCE_TYPE_MONEY    ,'amount' => 3 ]],
                
                ['src' => ['type' => RESOURCE_TYPE_SILK ,'amount' =>1 ],    'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_SILK ,'amount' =>1 ],    'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_RICE ,'amount' =>1 ],    'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_RICE ,'amount' =>1 ],    'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_POTTERY ,'amount' =>1 ], 'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_POTTERY ,'amount' =>1 ], 'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_SILK   ,'amount' =>1 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_RICE   ,'amount' =>1 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_POTTERY,'amount' =>1 ]],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    
    // -------------------------------------------------
    public function test_Args_UniTrade2Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_TRADE_POINTS;
        $currentBonusDatas = ['points'=>2,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $expectedArgs = [
            'skip' => true,
            'c' => $currentBonus,
            'nb' => 1,
            'trades' => [
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_SILK     ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_RICE     ,'amount' => 1 ]],
                ['src' => ['type' => BONUS_TYPE_POINTS,'amount' => 2 ], 'dest' => ['type' => RESOURCE_TYPE_POTTERY  ,'amount' => 1 ]],
                
                ['src' => ['type' => RESOURCE_TYPE_SILK ,'amount' =>1 ],    'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
                ['src' => ['type' => RESOURCE_TYPE_RICE ,'amount' =>1 ],    'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
                ['src' => ['type' => RESOURCE_TYPE_POTTERY ,'amount' =>1 ], 'dest' => ['type' => BONUS_TYPE_POINTS    ,'amount' =>2 ]],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $state->getArgs();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_UniTrade3Koku(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        Globals::setChoices(0);
        $currentBonus = BONUS_TYPE_TRADE_KOKU;
        $currentBonusDatas = ['koku'=>3,'bonusQuantity'=>1,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $expectedArgs = [
            'skip' => true,
            'c' => $currentBonus,
            'nb' => 1,
            'trades' => [
                ['src' => ['type' => RESOURCE_TYPE_SILK ,'amount' =>1 ],    'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_RICE ,'amount' =>1 ],    'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_POTTERY ,'amount' =>1 ], 'dest' => ['type' => RESOURCE_TYPE_MONEY  ,'amount' =>3 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_SILK   ,'amount' =>1 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_RICE   ,'amount' =>1 ]],
                ['src' => ['type' => RESOURCE_TYPE_MONEY ,'amount' => 3 ],  'dest' => ['type' => RESOURCE_TYPE_POTTERY,'amount' =>1 ]],
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
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    public function test_EnteringState_Pass_NoMoreTrades(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>0,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    // -------------------------------------------------

    public function test_ActionSkipBonuses_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        $args = $state->getArgs();

        $newState = $state->actSkipBonuses(999999, 1, $args);
        
        assertSame(0, Globals::getCurrentBonus());
        assertSame(null, Globals::getCurrentBonusDatas());
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------

    public function test_ActionMultiTrade_Pass_2Points_Money(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_MONEY;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(17, TestDatas::$players[1]['player_score']);//-2
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(8, $resources[RESOURCE_TYPE_MONEY]);//+3
        assertSame(['bonusQuantity'=>0,'points'=>2,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    
    public function test_ActionMultiTrade_Pass_2Points_Rice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_RICE;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(17, TestDatas::$players[1]['player_score']);//-2
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resources[RESOURCE_TYPE_RICE]);//+1
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(5, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(['bonusQuantity'=>0,'points'=>2,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }

    public function test_ActionMultiTrade_Pass_Silk_2Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>2,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_SILK;
        $dest = BONUS_TYPE_POINTS;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(21, TestDatas::$players[1]['player_score']);//+2
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resources[RESOURCE_TYPE_SILK]);//-1
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(5, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(['bonusQuantity'=>1,'points'=>2,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    public function test_ActionMultiTrade_Pass_Silk_Money(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>3,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_SILK;
        $dest = RESOURCE_TYPE_MONEY;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resources[RESOURCE_TYPE_SILK]);//-1
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(8, $resources[RESOURCE_TYPE_MONEY]);//+3
        assertSame(['bonusQuantity'=>2,'points'=>2,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    public function test_ActionMultiTrade_Pass_Silk_2Points_Unique(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_TRADE_POINTS;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_SILK;
        $dest = BONUS_TYPE_POINTS;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(21, TestDatas::$players[1]['player_score']);//+2
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resources[RESOURCE_TYPE_SILK]);//-1
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(5, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(['bonusQuantity'=>0,'points'=>2,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    public function test_ActionMultiTrade_Pass_Money_3Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_3;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>3,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_MONEY;
        $dest = BONUS_TYPE_POINTS;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(22, TestDatas::$players[1]['player_score']);//+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);//-3
        assertSame(['bonusQuantity'=>0,'points'=>3,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    
    public function test_ActionMultiTrade_Pass_Money_3Points_claimMastery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_3;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>3,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['player_score'] = 29;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        TestDatas::$tiles[1]['type'] = 15;
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_MONEY;
        $dest = BONUS_TYPE_POINTS;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(37, TestDatas::$players[1]['player_score']);//29+3+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);//-3
        assertSame(['bonusQuantity'=>0,'points'=>3,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    public function test_ActionMultiTrade_Pass_Money_Pottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_3;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>3,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_MONEY;
        $dest = RESOURCE_TYPE_POTTERY;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(3, $resources[RESOURCE_TYPE_POTTERY]);//+1
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);//-3
        assertSame(['bonusQuantity'=>0,'points'=>3,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }
    
    public function test_ActionMultiTrade_Pass_Money_Pottery_Unique(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_TRADE_KOKU;
        $currentBonusDatas = ['bonusQuantity'=>1,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":2,"3":1,"4":3,"5":1,"6":5}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_MONEY;
        $dest = RESOURCE_TYPE_POTTERY;

        $newState = $state->actMultiTrade($src,$dest,999999, 1, $args);
        
        //check RESOURCES ANd points :
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(3, $resources[RESOURCE_TYPE_POTTERY]);//+1
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);//-3
        assertSame(['bonusQuantity'=>0,'koku'=>3,], Globals::getCurrentBonusDatas());
        assertSame(BonusMultiTrades::class, $newState);
    }

    public function test_ActionMultiTrade_KO_lowPoints(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['player_score'] = 1;//not enough score to trade
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    
    public function test_ActionMultiTrade_KO_noSilk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":1,"3":1,"4":3,"5":1,"6":25}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_SILK;
        $dest = BONUS_TYPE_POINTS;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    public function test_ActionMultiTrade_KO_noPottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":1,"2":0,"3":1,"4":3,"5":1,"6":25}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_POTTERY;
        $dest = BONUS_TYPE_POINTS;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    public function test_ActionMultiTrade_KO_noRice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":1,"2":1,"3":0,"4":3,"5":1,"6":25}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_RICE;
        $dest = BONUS_TYPE_POINTS;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    public function test_ActionMultiTrade_KO_lowMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":1,"2":1,"3":0,"4":3,"5":1,"6":2}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_MONEY;
        $dest = BONUS_TYPE_POINTS;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }

    public function test_ActionMultiTrade_KO_maxMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":1,"6":25}';
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    
    public function test_ActionMultiTrade_KO_maxSilk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":6,"2":0,"3":0,"4":3,"5":1,"6":0}';
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_SILK;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    public function test_ActionMultiTrade_KO_maxPottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":2,"2":6,"3":1,"4":3,"5":1,"6":0}';
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_POTTERY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    public function test_ActionMultiTrade_KO_maxRice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":2,"2":0,"3":6,"4":3,"5":1,"6":0}';
        $args = $state->getArgs();
        $src = BONUS_TYPE_POINTS;
        $dest = RESOURCE_TYPE_RICE;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    
    public function test_ActionMultiTrade_KO_Silk_Rice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":2,"2":2,"3":2,"4":3,"5":1,"6":0}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_SILK;
        $dest = RESOURCE_TYPE_RICE;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    public function test_ActionMultiTrade_KO_Rice_Silk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
        $currentBonus = BONUS_TYPE_MULTITRADE_2;
        $currentBonusDatas = ['bonusQuantity'=>1,'points'=>2,'koku'=>3,];
        Globals::setCurrentBonus($currentBonus);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        TestDatas::$players[1]['resources'] = '{"1":2,"2":2,"3":2,"4":3,"5":1,"6":0}';
        $args = $state->getArgs();
        $src = RESOURCE_TYPE_RICE;
        $dest = RESOURCE_TYPE_SILK;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Invalid src $src / dest $dest");
        $state->actMultiTrade($src,$dest,999999, 1, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
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
        $state = new BonusMultiTrades($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusMultiTrades($game);
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
        $state = new BonusMultiTrades($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}