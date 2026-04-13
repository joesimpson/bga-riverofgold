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

final class BonusSetDieTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_BonusSetDie(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SET_DIE;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":1,"6":0}';
        Globals::setChoices(0);
        $expectedArgs = [
            'p' => [
                ['face' => 1, 'cost' => 1],
                ['face' => 2, 'cost' => 1],
                ['face' => 3, 'cost' => 1],
                ['face' => 4, 'cost' => 1],
                ['face' => 5, 'cost' => 1],
                ['face' => 6, 'cost' => 1],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusSetDie();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionFavorSelect_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SET_DIE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":2,"6":14}';
        $dieFace = 2;

        $game->actBonusSetDie($dieFace);
        
        assertSame('[]', TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, TestDatas::$players[TestDatas::$test_activePlayerId]['die_face']);
        assertSame(1, TestDatas::$players[1]['skip_roll_die']);
    }
    
    public function test_ActionFavorSelect_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_SET_DIE;
        $dieFace = 5;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot set the die to $dieFace");
        $game->actBonusSetDie($dieFace);
    }
    
    // -------------------------------------------------
}