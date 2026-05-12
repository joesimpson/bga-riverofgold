<?php

declare(strict_types=1);

namespace Tests\Managers;

use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\MasteryCard;
use ROG\Models\Player;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;

final class PlayersTest extends TestCase
{
    // -------------------------------------------------
    // -------------------------------------------------
    public function test_NextPlayerNotEliminated_SamePlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 1;
        $game = new GameMock();
        TestDatas::$players[2]['player_eliminated'] = true;
        TestDatas::$players[1]['player_eliminated'] = false;

        $nextPlayer = Players::getNextPlayerNotEliminated($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }
    public function test_NextPlayerNotEliminated_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 2;
        $game = new GameMock();
        TestDatas::$players[1]['player_eliminated'] = true;
        TestDatas::$players[2]['player_eliminated'] = false;

        $nextPlayer = Players::getNextPlayerNotEliminated($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }
    // -------------------------------------------------
    public function test_NextPlayerWithBonusToChoose_SamePlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 1;
        $game = new GameMock();
        TestDatas::$players[2]['bonuses'] = '['.BONUS_TYPE_CHOICE.']';
        TestDatas::$players[1]['bonuses'] = '['.BONUS_TYPE_DRAW.']';

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }

    
    public function test_NextPlayerWithBonusToChoose_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 1;
        $game = new GameMock();
        TestDatas::$players[2]['bonuses'] = '[]';
        TestDatas::$players[1]['bonuses'] = '['.BONUS_TYPE_DRAW.']';

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }

    public function test_NextPlayerWithBonusToChoose_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $expectedPlayerId = 2;
        $game = new GameMock();
        TestDatas::$players[1]['bonuses'] = '[]';
        TestDatas::$players[2]['bonuses'] = '['.BONUS_TYPE_DRAW.']';

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertTrue( $nextPlayer instanceof Player );
        self::assertSame( $expectedPlayerId, $nextPlayer->getId(), );
    }
    
    
    public function test_NextPlayerWithBonusToChoose_null(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        TestDatas::$test_activePlayerId = 1;
        $player_id = TestDatas::$test_activePlayerId;
        $game = new GameMock();
        TestDatas::$players[1]['bonuses'] = '[]';
        TestDatas::$players[2]['bonuses'] = '[]';

        $nextPlayer = Players::getNextPlayerWithBonusToChoose($player_id);
        
        self::assertNull($nextPlayer);
    }
    // -------------------------------------------------
    public function test_gainInfluence_Region1_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_Region1_ToSpace2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region1_ToSpace5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 5;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region1_ToSpace9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 9;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region1_ToSpace11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 11;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region1_ToSpace13(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 13;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region1_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $amount = 18;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    
    // -------------------------------------------------
    
    public function test_gainInfluence_Region2_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_Region2_ToSpace2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region2_ToSpace5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 5;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region2_ToSpace9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 9;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region2_ToSpace11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 11;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region2_ToSpace13(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 13;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region2_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 2;
        $amount = 18;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    // -------------------------------------------------
    
    public function test_gainInfluence_Region3_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_Region3_ToSpace2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region3_ToSpace5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 5;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region3_ToSpace9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 9;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region3_ToSpace11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 11;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region3_ToSpace13(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 13;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region3_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 3;
        $amount = 18;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    // -------------------------------------------------
    public function test_gainInfluence_Region4_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_Region4_ToSpace2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region4_ToSpace5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 5;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region4_ToSpace9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 9;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region4_ToSpace11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 11;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region4_ToSpace13(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 13;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region4_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 4;
        $amount = 18;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    // -------------------------------------------------
    
    public function test_gainInfluence_Region5_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_Region5_ToSpace2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region5_ToSpace5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 5;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region5_ToSpace9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 9;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region5_ToSpace11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 11;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region5_ToSpace13(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 13;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region5_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 5;
        $amount = 18;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    // -------------------------------------------------
    
    public function test_gainInfluence_Region6_ToSpace1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_Region6_ToSpace2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region6_ToSpace5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 5;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region6_ToSpace9(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 9;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region6_ToSpace11(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 11;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region6_ToSpace13(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 13;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_Region6_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 18;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    // -------------------------------------------------
    
    public function test_gainInfluence_RegionCustom1_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $regionCustom = 1;
        $amount = 18;
        Globals::setRegionCustomTracks([$region => $regionCustom]);

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5 FLOWER +3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(5, $resources[RESOURCE_TYPE_MOON]);//+2
        assertSame(4, $resources[RESOURCE_TYPE_SUN]);//+4
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    
    public function test_gainInfluence_RegionCustom2_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $regionCustom = 2;
        $amount = 18;
        Globals::setRegionCustomTracks([$region => $regionCustom]);

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5 FLOWER +1+2+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(14, $resources[RESOURCE_TYPE_MONEY]);//+14
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_RegionCustom3_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $regionCustom = 3;
        $amount = 18;
        Globals::setRegionCustomTracks([$region => $regionCustom]);

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5 FLOWER +3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(2, $resources[RESOURCE_TYPE_SILK]);//+2
        assertSame(2, $resources[RESOURCE_TYPE_POTTERY]);//+2
        assertSame(2, $resources[RESOURCE_TYPE_RICE]);//+2
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    public function test_gainInfluence_RegionCustom4_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $regionCustom = 4;
        $amount = 18;
        Globals::setRegionCustomTracks([$region => $regionCustom]);

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(36, TestDatas::$players[1]['player_score']);//19+5 FLOWER +1+2+4+5
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(false, $goToBonusChoice);
    }
    public function test_gainInfluence_RegionCustom5_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $regionCustom = 5;
        $amount = 18;
        Globals::setRegionCustomTracks([$region => $regionCustom]);
        $expectedBonuses = [
            ['region'=>$region,'bonusQuantity'=>1,'type' => BONUS_TYPE_INF_SELECT_REGION,],
            ['region'=>$region,'bonusQuantity'=>1,'type' => BONUS_TYPE_INF_SELECT_REGION,],
            ['region'=>$region,'bonusQuantity'=>1,'type' => BONUS_TYPE_INF_SELECT_REGION,],
            ['region'=>$region,'bonusQuantity'=>1,'type' => BONUS_TYPE_INF_SELECT_REGION,],
            ['region'=>$region,'bonusQuantity'=>3,'type' => BONUS_TYPE_INF_SELECT_REGION,],
        ];

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5 FLOWER +1+2+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame($expectedBonuses, json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    public function test_gainInfluence_RegionCustom6_ToSpace18(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 1;
        $regionCustom = 6;
        $amount = 18;
        Globals::setRegionCustomTracks([$region => $regionCustom]);
        $expectedBonuses = [
            ['region'=>$region,'bonusQuantity'=>1,'type' => BONUS_TYPE_MULTITRADE_2,],
            ['region'=>$region,'bonusQuantity'=>1,'type' => BONUS_TYPE_MULTITRADE_3,],
            ['region'=>$region,'bonusQuantity'=>2,'type' => BONUS_TYPE_MULTITRADE_2,],
            ['region'=>$region,'bonusQuantity'=>3,'type' => BONUS_TYPE_MULTITRADE_2,],
            ['region'=>$region,'bonusQuantity'=>4,'type' => BONUS_TYPE_MULTITRADE_2,],
        ];

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5 FLOWER
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame($expectedBonuses, json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(true, $goToBonusChoice);
    }
    // -------------------------------------------------
    
    public function test_gainInfluence_LadyOfWhispers_Jump1Player(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 16;
        TestDatas::$cards[101]['type'] = PATRON_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 1;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount + 1, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(27, TestDatas::$players[1]['player_score']);//19+5+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
    }
    public function test_gainInfluence_LadyOfWhispers_Jump2Players(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 16;
        TestDatas::$cards[101]['type'] = PATRON_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 1;
        TestDatas::$tokens[17] = TestDatas::$tokens[16];
        TestDatas::$tokens[17]['meeple_state'] = 3;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount + 2, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        //!\ return value is currently not updated by the recursive calls, we should not rely on the return value
        //assertSame(true, $goToBonusChoice);
    }
    
    public function test_gainInfluence_LadyOfWhispers_Jump3Players(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 15;
        TestDatas::$cards[101]['type'] = PATRON_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 1;
        TestDatas::$tokens[17] = TestDatas::$tokens[16];
        TestDatas::$tokens[17]['meeple_state'] = 3;
        TestDatas::$tokens[18] = TestDatas::$tokens[16];
        TestDatas::$tokens[18]['meeple_state'] = 17;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        
        assertSame($amount + 3, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(30, TestDatas::$players[1]['player_score']);//19+5+3+3
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(2, $resources[RESOURCE_TYPE_MONEY]);
        assertSame([BONUS_TYPE_CHOICE], json_decode(TestDatas::$players[1]['bonuses'], true));
        //!\ return value is currently not updated by the recursive calls, we should not rely on the return value
        //assertSame(true, $goToBonusChoice);
    }
    
    // -------------------------------------------------
    
    public function test_gainInfluence_GovernorCityOfLies_KO_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 3;
        TestDatas::$cards[104]['type'] = PATRON_GOVERNOR;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 0;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertSame(2,  TestDatas::$players[2]['player_score']);//2+0
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses'], true));
    }
    public function test_gainInfluence_GovernorCityOfLies_Pass_2_Passed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 3;
        TestDatas::$cards[104]['type'] = PATRON_GOVERNOR;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertSame(5,  TestDatas::$players[2]['player_score']);//2+3
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses'], true));
    }
    public function test_gainInfluence_GovernorCityOfLies_KO_2_Reached(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 2;
        TestDatas::$cards[104]['type'] = PATRON_GOVERNOR;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertSame(2,  TestDatas::$players[2]['player_score']);//2+0
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses'], true));
    }
    public function test_gainInfluence_GovernorCityOfLies_Pass_VS_Lady(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $region = 6;
        $amount = 2;
        TestDatas::$cards[101]['type'] = PATRON_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[104]['type'] = PATRON_GOVERNOR;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[16]['meeple_state'] = 2;

        $goToBonusChoice = Players::gainInfluence($player,$region,$amount);
        
        assertSame(3, TestDatas::$tokens[$region]['meeple_state']);
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertSame(5,  TestDatas::$players[2]['player_score']);//2+3
        assertSame([], json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame([], json_decode(TestDatas::$players[2]['bonuses'], true));
    }
    // -------------------------------------------------
    
    public function test_claimMasteries_Type1_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_1;
        TestDatas::$cards[13] = TestDatas::$cards[11];

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);
    }
    public function test_claimMasteries_Type1_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_1;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MERCHANT_1;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    public function test_claimMasteries_Type1_Active_2Players_Last(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_1;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MERCHANT_1;
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);
        assertFalse( array_key_exists(102,TestDatas::$tokens));//no new clan marker
    }
    
    public function test_claimMasteries_Active_2Players_First_ScionOfEarth(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":1,"5":0,"6":0}';
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_1;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MERCHANT_1;
        //with patron abiliy
        TestDatas::$cards[101]['type'] = PATRON_SCION_OF_EARTH;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5+0
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(2, $resources[RESOURCE_TYPE_MOON]);//+1
        assertSame(2, $resources[RESOURCE_TYPE_SUN]);//+3 max 2
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_claimMasteries_Active_2Players_Last_ScionOfEarth(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":2,"5":0,"6":0}';
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_1;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MERCHANT_1;
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        //with patron abiliy
        TestDatas::$cards[101]['type'] = PATRON_SCION_OF_EARTH;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        $newClanMarker = TestDatas::$tokens[102];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(2, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);//+1
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);//+3
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_claimMasteries_Type1_Active_2Players_NewCustomers(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type']  = CARD_SMUGGLER_2;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_1;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_SHINDOSHI_5;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    
    public function test_claimMasteries_Type1_Active_4Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 7;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_ELDER_3;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MONK_4;

        Players::claimMastery($player,$tile);
        
        assertSame(26, TestDatas::$players[1]['player_score']);//19+7
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    public function test_claimMasteries_Type1_Active_4Players_Second(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 7;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_NOBLE_2;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MONK_4;
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        $newClanMarker = TestDatas::$tokens[102];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(2, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    public function test_claimMasteries_Type1_Active_4Players_Third(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 7;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_NOBLE_2;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MONK_4;
        //2 opponents on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(22, TestDatas::$players[1]['player_score']);//19+3
        $newClanMarker = TestDatas::$tokens[103];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(3, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
    }
    public function test_claimMasteries_Type1_Active_4Players_Last(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 7;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type']  = CARD_NOBLE_2;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[13]['type']  = CARD_MONK_4;
        //2 opponents on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[103] = ['result_associative_index' => 103, 'meeple_id' => 103, 'meeple_state' => 3, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19
        assertFalse( array_key_exists(104,TestDatas::$tokens));//no new clan marker
    }
    
    public function test_claimMasteries_Type4_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 4;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    public function test_claimMasteries_Type4_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 4;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    public function test_claimMasteries_Type4_Active_2Players_Last(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);
    }
    public function test_claimMasteries_Type4_Active_2Players_newCustomers(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 4;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_TRADER_2;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        TestDatas::$cards[12]['type'] = CARD_TRADER_5;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    public function test_claimMasteries_Type4_Active_4Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];

        Players::claimMastery($player,$tile);
        
        assertSame(26, TestDatas::$players[1]['player_score']);//19+7
    }
    
    public function test_claimMasteries_Type4_Active_4Players_First_ScionOfEarth(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //with patron abiliy
        TestDatas::$cards[101]['type'] = PATRON_SCION_OF_EARTH;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        Players::claimMastery($player,$tile);
        
        assertSame(26, TestDatas::$players[1]['player_score']);//19+7+0
        $newClanMarker = TestDatas::$tokens[43];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(1, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//+1
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);//+3
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_claimMasteries_Type4_Active_4Players_Second(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    public function test_claimMasteries_Type4_Active_4Players_SecondAgain_ScionOfEarth(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //3 opponents on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        //+ 1 clan marker already placed for this player
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ];
        //with patron abiliy
        TestDatas::$cards[101]['type'] = PATRON_SCION_OF_EARTH;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertFalse( array_key_exists(103,TestDatas::$tokens));//no new clan marker
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);//+0
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);//+0
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_claimMasteries_Type4_Active_4Players_Third(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(22, TestDatas::$players[1]['player_score']);//19+3
    }
    public function test_claimMasteries_Type4_Active_4Players_Last(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //1 opponent on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[103] = ['result_associative_index' => 103, 'meeple_id' => 103, 'meeple_state' => 3, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19
    }
    public function test_claimMasteries_Type4_Active_4Players_Last_ScionOfEarth(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //3 opponents on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[103] = ['result_associative_index' => 103, 'meeple_id' => 103, 'meeple_state' => 3, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        //with patron abiliy
        TestDatas::$cards[101]['type'] = PATRON_SCION_OF_EARTH;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        $newClanMarker = TestDatas::$tokens[104];
        assertSame(MEEPLE_LOCATION_TILE.'1', $newClanMarker['meeple_location']);
        assertSame(4, $newClanMarker['meeple_state']);
        assertSame(1, $newClanMarker['player_id']);
        assertSame(MEEPLE_TYPE_CLAN_MARKER, $newClanMarker['type']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(4, $resources[RESOURCE_TYPE_MOON]);//+1
        assertSame(3, $resources[RESOURCE_TYPE_SUN]);//+3
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    public function test_claimMasteries_Type4_Active_4Players_LastAgain_ScionOfEarth(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 10;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12] = TestDatas::$cards[11];
        //3 opponents on the mastery tile :
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 2, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[103] = ['result_associative_index' => 103, 'meeple_id' => 103, 'meeple_state' => 3, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        //+ 1 clan marker already placed for this player
        TestDatas::$tokens[104] = ['result_associative_index' => 104, 'meeple_id' => 104, 'meeple_state' => 4, 'meeple_location'=> MEEPLE_LOCATION_TILE.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ];
        //with patron abiliy
        TestDatas::$cards[101]['type'] = PATRON_SCION_OF_EARTH;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertFalse( array_key_exists(105,TestDatas::$tokens));//no new clan marker
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);//+0
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);//+0
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_claimMasteries_Type3_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 3;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tiles[44]['type'] = 20;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    public function test_claimMasteries_Type3_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 3;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    
    public function test_claimMasteries_Type5_Inactive_2Players(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 5;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 0;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 1;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    public function test_claimMasteries_Type5_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 5;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 1;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    
    // Used for Scion of Void
    public function test_claimMasteries_Reserved_ForMe(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        TestDatas::$tiles[5]['player_id'] = 1;
        TestDatas::$tiles[5]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 1;

        Players::claimMasteries($player);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        assertSame(2, TestDatas::$players[2]['player_score']);//2+0
    }
    public function test_claimMasteries_Reserved_ForOpponent(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        TestDatas::$tiles[5]['player_id'] = 2;
        TestDatas::$tiles[5]['tile_location'] = TILE_LOCATION_MASTERY_RESERVED;
        TestDatas::$tokens[1]['meeple_state'] = 1;
        TestDatas::$tokens[2]['meeple_state'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 1;
        TestDatas::$tokens[6]['meeple_state'] = 1;

        Players::claimMasteries($player);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
        assertSame(2, TestDatas::$players[2]['player_score']);//2+0
    }
    
    public function test_claimMasteries_Type6_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 6;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['type'] = 19;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[45] = TestDatas::$tokens[41];
        TestDatas::$tokens[45]['meeple_location'] = MEEPLE_LOCATION_TILE.'45';
        TestDatas::$tiles[45] = TestDatas::$tiles[41];
        TestDatas::$tiles[45]['tile_id'] = 45;
        TestDatas::$tiles[45]['result_associative_index'] = 45;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    public function test_claimMasteries_Type6_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 6;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['type'] = 19;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[45] = TestDatas::$tokens[41];
        TestDatas::$tokens[45]['meeple_location'] = MEEPLE_LOCATION_TILE.'45';
        TestDatas::$tiles[45] = TestDatas::$tiles[41];
        TestDatas::$tiles[45]['tile_id'] = 45;
        TestDatas::$tiles[45]['type'] = 13;
        TestDatas::$tiles[45]['result_associative_index'] = 45;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    
    // -------------------------------------------------
    public function test_claimMasteries_typeWave_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 13;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['type'] = 19;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 10;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tiles[44]['tile_state'] = 11;

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    public function test_claimMasteries_typeWaves_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 13;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['type'] = 19;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 10;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tiles[44]['tile_state'] = 12;

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    
    // -------------------------------------------------
    public function test_claimMasteries_typeSunMoon_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":4,"6":0}';
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 14;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);

        Players::claimMastery($player,$tile);
        
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    public function test_claimMasteries_typeSunMoon_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":5,"6":0}';
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 14;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);

        Players::claimMastery($player,$tile);
        
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }
    
    // -------------------------------------------------
    public function test_claimMasteries_typeLightning_Inactive(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['player_score'] = 29;
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 15;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);

        Players::claimMastery($player,$tile);
        
        assertSame(29, TestDatas::$players[1]['player_score']);//29+0
    }
    public function test_claimMasteries_typeLightning_Active_2Players_First(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['player_score'] = 30;
        $player = Players::get(1);
        $tileRow = TestDatas::$tiles[1];
        $tileRow['type'] = 15;
        $tile = new MasteryCard($tileRow, Tiles::getMasteryCardsTypes()[ $tileRow['type']]);

        Players::claimMastery($player,$tile);
        
        assertSame(35, TestDatas::$players[1]['player_score']);//30+5
    }
    public function test_claimMasteries_typeLightning_Active_AfterOtherMasteries(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":5,"6":0}';
        TestDatas::$players[1]['player_score'] = 25;
        $player = Players::get(1);
        TestDatas::$tiles[1]['type'] = 15;
        TestDatas::$tiles[2]['type'] = 14;

        Players::claimMasteries($player);
        
        assertSame(35, TestDatas::$players[1]['player_score']);//25+5+5
    }
    
    // -------------------------------------------------
}