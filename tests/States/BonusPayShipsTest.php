<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use Bga\Games\RiverOfGoldNightMarket\States\BonusPayShips;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Exceptions\UserException;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BonusPayShipsTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        Globals::setChoices(0);
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":1}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        $expectedArgs = [
            'ships_pids' => [2],
            'max' => 1,
            'cost_pp' => 1,
            'points_pp' => 2,
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
        $state = new BonusPayShips($game);
        $args = $state->getArgs();

        $newState = $state->onEnteringState(1, $args);
        
        assertSame(null, $newState);
    }
    // -------------------------------------------------
 
    public function test_ActionPayShips_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":1}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        $p_ids = [2];
        $args = $state->getArgs();

        $newState = $state->actPayShips($p_ids, 999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(21, TestDatas::$players[1]['player_score']);//+2
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(1, $resourcesP2[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionPayShips_Pass_NoSelection(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":1}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        $p_ids = [];
        $args = $state->getArgs();

        $newState = $state->actPayShips($p_ids, 999999, 1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_MONEY]);
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionPayShips_Pass_5Players_All(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        TestDatas::$players[5] = TestDatas::$players[1];
        TestDatas::$players[5]['player_id'] = 5;
        TestDatas::$players[5]['result_associative_index'] = 5;
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":10}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        TestDatas::$tokens[25] = TestDatas::$tokens[23];
        TestDatas::$tokens[25]['meeple_id'] = 25;
        TestDatas::$tokens[25]['result_associative_index'] = 25;
        TestDatas::$tokens[25]['player_id'] = 3;
        TestDatas::$tokens[26] = TestDatas::$tokens[25];
        TestDatas::$tokens[26]['meeple_id'] = 26;
        TestDatas::$tokens[26]['result_associative_index'] = 26;
        TestDatas::$tokens[26]['player_id'] = 4;
        TestDatas::$tokens[27] = TestDatas::$tokens[25];
        TestDatas::$tokens[27]['meeple_id'] = 27;
        TestDatas::$tokens[27]['result_associative_index'] = 27;
        TestDatas::$tokens[27]['player_id'] = 5;
        $p_ids = [2,3,4,5];
        $args = $state->getArgs();

        $newState = $state->actPayShips($p_ids, 999999, 1, $args);
        
        assertSame(27, TestDatas::$players[1]['player_score']);//+2*4
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(6, $resourcesP1[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[2]['resources'], true)[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[3]['resources'], true)[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[4]['resources'], true)[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[5]['resources'], true)[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionPayShips_Pass_5Players_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        TestDatas::$players[3] = TestDatas::$players[1];
        TestDatas::$players[3]['player_id'] = 3;
        TestDatas::$players[3]['result_associative_index'] = 3;
        TestDatas::$players[4] = TestDatas::$players[1];
        TestDatas::$players[4]['player_id'] = 4;
        TestDatas::$players[4]['result_associative_index'] = 4;
        TestDatas::$players[5] = TestDatas::$players[1];
        TestDatas::$players[5]['player_id'] = 5;
        TestDatas::$players[5]['result_associative_index'] = 5;
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":10}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        TestDatas::$tokens[25] = TestDatas::$tokens[23];
        TestDatas::$tokens[25]['meeple_id'] = 25;
        TestDatas::$tokens[25]['result_associative_index'] = 25;
        TestDatas::$tokens[25]['player_id'] = 3;
        TestDatas::$tokens[26] = TestDatas::$tokens[25];
        TestDatas::$tokens[26]['meeple_id'] = 26;
        TestDatas::$tokens[26]['result_associative_index'] = 26;
        TestDatas::$tokens[26]['player_id'] = 4;
        TestDatas::$tokens[27] = TestDatas::$tokens[25];
        TestDatas::$tokens[27]['meeple_id'] = 27;
        TestDatas::$tokens[27]['result_associative_index'] = 27;
        TestDatas::$tokens[27]['player_id'] = 5;
        TestDatas::$tokens[27]['meeple_state'] = 1;
        $p_ids = [2,3,4,];
        $args = $state->getArgs();

        $newState = $state->actPayShips($p_ids, 999999, 1, $args);
        
        assertSame(25, TestDatas::$players[1]['player_score']);//+2*3
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(7, $resourcesP1[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[2]['resources'], true)[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[3]['resources'], true)[RESOURCE_TYPE_MONEY]);
        assertSame(1, json_decode(TestDatas::$players[4]['resources'], true)[RESOURCE_TYPE_MONEY]);
        assertSame(0, json_decode(TestDatas::$players[5]['resources'], true)[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionPayShips_KO_NoMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":0}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        $p_ids = [2];
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You must select 0 players max");
        $newState = $state->actPayShips($p_ids, 999999, 1, $args);
    }
    public function test_ActionPayShips_KO_WrongPlayers(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
        Globals::setLastSailedShip(21);
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":3}';
        TestDatas::$tokens[23]['meeple_state'] = TestDatas::$tokens[21]['meeple_state'];
        $p_ids = [3];
        $args = $state->getArgs();

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("These players are not a valid set");
        $newState = $state->actPayShips($p_ids, 999999, 1, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionRestart_KO_WrongVersion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actRestart(1, 1,);
    }
    
    public function test_ActionRestart_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
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
        $state = new BonusPayShips($game);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("!!!checkVersion");
        $state->actUndoToStep(1, 1,);
    }
    public function test_ActionUndo_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $state = new BonusPayShips($game);
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
        $state = new BonusPayShips($game);
        $args = $state->getArgs();

        $newState = $state->zombie(1, $args);
        
        assertSame(ST_BONUS_CHOICE, $newState);
    }
    
    // -------------------------------------------------
}