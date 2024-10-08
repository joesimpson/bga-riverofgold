{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- RiverOfGold implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->


<div id="rog_game_container">
    
    <div id="rog_end_score_recap"></div>

    <div id="rog_select_piece_container"></div>
    
    <div id="rog_upper_zone">
        <div id='rog_mastery_cards'></div>
        <div id="rog_era_tile_resizable">
            <div id="rog_era_tile_holder" class="rog_era_tile_holder">
                <div id='rog_building_era-1' class="rog_building_slot"></div>
                <div id='rog_building_era-2' class="rog_building_slot"></div>
                <div id='rog_deck_size-1' class="rog_deck_size"></div>
                <div id='rog_deck_size-2' class="rog_deck_size"></div>
            </div>
        </div>
    </div>
    <div id="rog_main_zone">
        <div id='rog_resizable_river_board'>
            <div id='rog_river_board_container'>
                <div id='rog_river_board'>
                    <div id='rog_score_track'></div>
                    <div id='rog_score_customers'></div>
                    <div id='rog_scoring_tiles'>
                        <div id='rog_scoring_tile-1'></div>
                        <div id='rog_scoring_tile-2'></div>
                        <div id='rog_scoring_tile-3'></div>
                        <div id='rog_scoring_tile-4'></div>
                        <div id='rog_scoring_tile-5'></div>
                        <div id='rog_scoring_tile-6'></div>
                    </div>
                    <div id='rog_shore_spaces'></div>
                    <div id='rog_river_spaces'></div>
                    <div id='rog_influence_tracks'></div>
                    <div id='rog_artisan_spaces'></div>
                    <div id='rog_elder_spaces'></div>
                    <div id='rog_complete_journey'>
                        <div id='rog_merchant_space'></div>
                    </div>
                    <div id='rog_building_bonus_favor'></div>
                    <div id='rog_building_row'>
                        <div id='rog_building_slot-4' class="rog_building_slot"></div>
                        <div id='rog_building_slot-3' class="rog_building_slot"></div>
                        <div id='rog_building_slot-2' class="rog_building_slot"></div>
                        <div id='rog_building_slot-1' class="rog_building_slot"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="rog_players_boards">
            <div id="rog_players_deliveries">
            </div>
        </div>
    </div>

</div>


<svg style="display:none" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-question" role="img" xmlns="http://www.w3.org/2000/svg">
    <symbol id="help-marker-svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="white" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="1"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g>
    </symbol>
</svg>
{OVERALL_GAME_FOOTER}
