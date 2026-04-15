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

final class BonusResourceTest extends TestCase
{


    // -------------------------------------------------
    // -------------------------------------------------
    public function test_Args_NoResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        //all at max
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":6,"2":6,"3":6,"4":0,"5":0,"6":0}';
        $expectedArgs = [
            'p' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusResource();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_SomeResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":6,"3":2,"4":0,"5":0,"6":0}';
        $expectedArgs = [
            'p' => [ RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE, ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusResource();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_AllResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $expectedArgs = [
            'p' => [RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE, RESOURCE_TYPE_POTTERY],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusResource();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
    
    // -------------------------------------------------
    public function test_ActionBonusResource_Pass_Silk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_SILK;

        $game->actBonusResource($resourceType);
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(4, $resources[$resourceType]);//+1
    }
    public function test_ActionBonusResource_Pass_Rice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_RICE;

        $game->actBonusResource($resourceType);
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(3, $resources[$resourceType]);//+1
    }
    public function test_ActionBonusResource_KO_WrongResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":6,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_POTTERY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot receive this resource ($resourceType)");
        $game->actBonusResource($resourceType);
    }
    public function test_ActionBonusResource_KO_WrongResource2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE_RESOURCE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $resourceType = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot receive this resource ($resourceType)");
        $game->actBonusResource($resourceType);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
}