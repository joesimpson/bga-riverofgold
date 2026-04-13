<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class SpendFavorTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_SpendFavor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DIVINE_FAVOR;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":1,"6":0}';
        Globals::setChoices(0);
        $expectedArgs = [
            'p' => [
                ['face' => 2, 'cost' => 1],
                ['face' => 6, 'cost' => 1],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSpendFavor();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionFavorSelect_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DIVINE_FAVOR;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":1,"6":0}';
        $dieFace = 2;

        $game->actDFSelect($dieFace);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionFavorSelect_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DIVINE_FAVOR;
        $dieFace = 5;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot change the die to $dieFace");
        $game->actDFSelect($dieFace);
    }
    
    // -------------------------------------------------
}