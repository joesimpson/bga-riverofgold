<?php
 
/*
 * Game Constants
 */
 const REGION_1 =  1;
 const REGION_2 =  2;
 const REGION_3 =  3;
 const REGION_4 =  4;
 const REGION_5 =  5;
 const REGION_6 =  6;

 const CUSTOMER_TYPE_ARTISAN =  1;
 const CUSTOMER_TYPE_ELDER =    2;
 const CUSTOMER_TYPE_MERCHANT = 3;
 const CUSTOMER_TYPE_MONK =     4;
 const CUSTOMER_TYPE_NOBLE =    5;

 const TILE_TYPE_SCORING = 1;
 const TILE_TYPE_BUILDING = 2;

const TILE_LOCATION_SCORING = 's';

 const CARD_LOCATION_DECK = 'deck';
 const CARD_LOCATION_DELIVERED = 'dd';
 const CARD_LOCATION_HAND = 'h';
 //! Warning one clan patron will have more cards (3)
 const NB_CARDS_PER_PLAYER = 2;
/*
 * Game options
 */ 
const OPTION_EXPANSION_CLANS = 110;
const OPTION_EXPANSION_CLANS_OFF = 0;
const OPTION_EXPANSION_CLANS_ON = 1;
/*
 * User preferences
 */  


/*
 * State constants
 */
const ST_GAME_SETUP = 1;

const ST_CLAN_SELECTION = 3;
const ST_PLAYER_SETUP = 5;
 
const ST_NEXT_TURN = 10;
const ST_BEFORE_TURN = 11;

const ST_PLAYER_TURN = 20;

const ST_END_SCORING = 90;
const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;
 