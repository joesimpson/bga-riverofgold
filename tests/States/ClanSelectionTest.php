<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;

use function PHPUnit\Framework\assertSame;

final class ClanSelectionTest extends TestCase
{

    public function testEnteringState_baseGame(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_OFF);
        $game->stClanSelection();
        
        assertSame(ST_PLAYER_SETUP, GamestateMachine::$test_current_state);
    }

    
    public function testEnteringState_ClanDraft(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_DRAFT);
        $game->stClanSelection();
        
        assertSame(ST_DRAFT_PLAYER, GamestateMachine::$test_current_state);
    }
      
    public function testEnteringState_ClanDraftAlternative(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_CLAN_SELECTION;
        Globals::setOptionClanPatrons(OPTION_EXPANSION_CLANS_ALTERNATIVE);
        $game->stClanSelection();
        
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
    }
     
}