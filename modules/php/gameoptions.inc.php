<?php

/**
 *------
  * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * RiverOfGold implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * River of Gold game options description
 * 
 * NB : 11/2023 new JSON format you can generate it from this file with PHP : 
 * call the debug function from chat :
 *    debugJSON()
 *
 */

namespace ROG;

//if placed at root folder
//require_once 'modules/php/constants.inc.php';
//Else near constants :
require_once 'constants.inc.php';

$game_options = [

  OPTION_EXPANSION_CLANS => array(
    'name' => 'Clans Patrons Mini Expansion',    
    'values' => [
      OPTION_EXPANSION_CLANS_OFF => [
        'name' => 'Disabled', 
        'description' => 'No specific clans for players', 
      ],
      OPTION_EXPANSION_CLANS_DRAFT => [
        'name' => 'Draft Setup', 
        'description' => 'Clans patrons give powerful new unique abilities to each clan : 1 random patron card is available for each of the 4 clans', 
        'tmdisplay' => 'Clans Patrons',
      ],
      OPTION_EXPANSION_CLANS_ALTERNATIVE => [
        'name' => 'Alternative Setup', 
        'description' => 'Clans patrons give powerful new unique abilities to each clan : each player receive a clan and then choose 1 of its patron cards', 
        'tmdisplay' => 'Clans Patrons Alternative',
      ],
    ],
    'default' => OPTION_EXPANSION_CLANS_OFF,
     
  ), 

];


$game_preferences = [

  PREF_PLAYER_PANEL_DETAILS => [
    'name' => totranslate('Player panel details'),
    'needReload' => false,
    'values' => [
      PREF_PLAYER_PANEL_DETAILS_FULL => [ 'name' => totranslate('Detailed') ],
      PREF_PLAYER_PANEL_DETAILS_COMPACT => [ 'name' => totranslate('Compact')],
    ],
    "default"=> PREF_PLAYER_PANEL_DETAILS_FULL,
    'attribute' => 'rog_panel_details',
  ],
  
  PREF_UNDO_STYLE => [
    'name' => totranslate('Undo buttons style'),
    'needReload' => false,
    'values' => [
      PREF_UNDO_STYLE_TEXT => [ 'name' => totranslate('Text') ],
      PREF_UNDO_STYLE_ICON => [ 'name' => totranslate('Icon')],
    ],
    "default"=> PREF_UNDO_STYLE_ICON,
    'attribute' => 'rog_undo_style',
  ],

  PREF_PLAYER_PANEL_BACKGROUND => [
    'name' => totranslate('Player panel background'),
    'needReload' => false,
    'values' => [
      PREF_PLAYER_PANEL_BACKGROUND_NONE => [ 'name' => totranslate('Disabled') ],
      PREF_PLAYER_PANEL_BACKGROUND_SYMBOL => [ 'name' => totranslate('Clan symbol')],
    ],
    "default"=> PREF_PLAYER_PANEL_BACKGROUND_SYMBOL,
    'attribute' => 'rog_panel_background',
  ],
  
  PREF_PLAYER_PANEL_BORDER => [
    'name' => totranslate('Player panel border'),
    'needReload' => false,
    'values' => [
      PREF_PLAYER_PANEL_BORDER_OFF => [ 'name' => totranslate('Disabled') ],
      PREF_PLAYER_PANEL_BORDER_ON => [ 'name' => totranslate('Enabled')],
    ],
    "default"=> PREF_PLAYER_PANEL_BORDER_ON,
    'attribute' => 'rog_panel_border',
  ],
  
  PREF_ANIMATION_SHIP_SELECTED => [
    'name' => totranslate('Selected ship animation (Disable for better performances)'),
    'needReload' => false,
    'values' => [
      PREF_ANIMATION_SHIP_SELECTED_OFF => [ 'name' => totranslate('Disabled') ],
      PREF_ANIMATION_SHIP_SELECTED_BOUNCE => [ 'name' => totranslate('Enabled')],
    ],
    "default"=> PREF_ANIMATION_SHIP_SELECTED_BOUNCE,
    'attribute' => 'rog_anim_ship_selected',
  ],
  
  PREF_ANIMATION_LASTTURN_MESSAGE => [
    'name' => totranslate('Last turn warning'),
    'needReload' => false,
    'values' => [
      PREF_ANIMATION_LASTTURN_NORMAL => [ 'name' => totranslate('Standard text') ],
      PREF_ANIMATION_LASTTURN_BLINK => [ 'name' => totranslate('Blinking')],
    ],
    "default"=> PREF_ANIMATION_LASTTURN_BLINK,
    'attribute' => 'rog_anim_lastturn_msg',
  ],
];
