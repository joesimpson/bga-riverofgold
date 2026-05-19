<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Players;
use ROG\Models\Player;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class BeforeTurnTest extends TestCase
{

    public function testEnteringState_noAction(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BEFORE_TURN;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;

        $game->stBeforeTurn();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_PatronDarling(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BEFORE_TURN;
        Globals::setTurn(1);
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$cards[101]['player_id'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $game->stBeforeTurn();
        
        //STAY IN state to play
        assertSame(ST_BEFORE_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BEFORE_TURN;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '[]';
        Globals::setChoices(0);
        $expectedArgs = [
            'p' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBeforeTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_ActionSkip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_BEFORE_TURN;
        $game->actSkip(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_listPossibleDieFacesToSet(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BEFORE_TURN;
        TestDatas::$players[1]['resources'] = json_encode([5=>2]);
        $player = Players::get(1);
        $expectedFaces = [
            ['face' => 1, 'cost' => 1],
            ['face' => 2, 'cost' => 1],
            ['face' => 3, 'cost' => 1],
            ['face' => 4, 'cost' => 1],
            ['face' => 5, 'cost' => 1],
            ['face' => 6, 'cost' => 1],
        ];

        $possibleFaces = $game->listPossibleDieFacesToSet($player);
        
        assertSame($expectedFaces, $possibleFaces);
    }

}