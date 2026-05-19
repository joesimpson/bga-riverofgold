<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class BonusSellGoodsTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    public function test_Args_NoSell(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        $expectedArgs = [
            'p' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusSellGoods();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_ResourcesToSell(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $expectedArgs = [
            'p' => [
                ['src' => [RESOURCE_TYPE_SILK => NB_RESOURCE_FOR_SELLING_MERCHANT_4], 'dest' => [RESOURCE_TYPE_MONEY => NB_MONEY_FOR_SELLING_MERCHANT_4 ],  ],
                ['src' => [RESOURCE_TYPE_RICE => NB_RESOURCE_FOR_SELLING_MERCHANT_4], 'dest' => [RESOURCE_TYPE_MONEY => NB_MONEY_FOR_SELLING_MERCHANT_4 ],  ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusSellGoods();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionStop_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;

        $game->actStop(999999);
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
    public function test_ActionSell_Pass_Silk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_SILK;

        $game->actSell($resourceType,999999);
        
        assertSame(ST_BONUS_SELL_GOODS, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(2, $resources[$resourceType]);//-1
        assertSame(5, $resources[RESOURCE_TYPE_MONEY]);//NB_MONEY_FOR_SELLING_MERCHANT_4
    }
    public function test_ActionSell_Pass_Pottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":1,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_POTTERY;

        $game->actSell($resourceType,999999);
        
        assertSame(ST_BONUS_SELL_GOODS, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(0, $resources[$resourceType]);//-1
        assertSame(5, $resources[RESOURCE_TYPE_MONEY]);//NB_MONEY_FOR_SELLING_MERCHANT_4
    }
    public function test_ActionSell_Pass_Rice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":1,"3":2,"4":0,"5":0,"6":1}';
        $resourceType = RESOURCE_TYPE_RICE;

        $game->actSell($resourceType,999999);
        
        assertSame(ST_BONUS_SELL_GOODS, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[$resourceType]);//-1
        assertSame(6, $resources[RESOURCE_TYPE_MONEY]);//NB_MONEY_FOR_SELLING_MERCHANT_4
    }
    public function test_ActionSell_KO_WrongResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_POTTERY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot sell this resource ($resourceType)");
        $game->actSell($resourceType,999999);
    }
    public function test_ActionSell_KO_WrongResource2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SELL_GOODS;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot sell this resource ($resourceType)");
        $game->actSell($resourceType,999999);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
}