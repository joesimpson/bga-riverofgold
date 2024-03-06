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
 const REGIONS = [
    REGION_1,
    REGION_2,
    REGION_3,
    REGION_4,
    REGION_5,
    REGION_6,
 ];

 const NB_MAX_MONEY = 25;
 const NB_MAX_RESOURCE = 6;
 const NB_MAX_INLFUENCE = 18;
 
 const CUSTOMER_TYPE_ARTISAN =  1;
 const CUSTOMER_TYPE_ELDER =    2;
 const CUSTOMER_TYPE_MERCHANT = 3;
 const CUSTOMER_TYPE_MONK =     4;
 const CUSTOMER_TYPE_NOBLE =    5;

 const TILE_TYPE_SCORING = 1;
 const TILE_TYPE_BUILDING = 2;
 const TILE_TYPE_MASTERY_CARD = 3;
 
 const BUILDING_TYPE_PORT =     1;
 const BUILDING_TYPE_MARKET =   2;
 const BUILDING_TYPE_MANOR =    3;
 const BUILDING_TYPE_SHRINE =   4;
 
 const MASTERY_TYPE_AIR    = 1; 
 const MASTERY_TYPE_COURTS = 2;
 const MASTERY_TYPE_EARTH  = 3;
 const MASTERY_TYPE_FIRE   = 4;
 const MASTERY_TYPE_VOID   = 5;
 const MASTERY_TYPE_WATER  = 6;

const TILE_LOCATION_SCORING = 's';
const TILE_LOCATION_MASTERY_CARD = 'm';
const TILE_LOCATION_BUILDING_DECK = 'bd';
const TILE_LOCATION_BUILDING_DECK_ERA_1 = TILE_LOCATION_BUILDING_DECK.'1';
const TILE_LOCATION_BUILDING_DECK_ERA_2 = TILE_LOCATION_BUILDING_DECK.'2';
const TILE_LOCATION_BUILDING_ROW ='br';
const TILE_LOCATION_BUILDING_SHORE = 'sh';

const BUILDING_ROW_END = 4;

 const CARD_LOCATION_DECK = 'deck';
 const CARD_LOCATION_DELIVERED = 'dd';
 const CARD_LOCATION_HAND = 'h';
 //! Warning one clan patron will have more cards (3)
 const NB_CARDS_PER_PLAYER = 2;


const RESOURCE_TYPE_SILK = 1;
const RESOURCE_TYPE_POTTERY = 2;
const RESOURCE_TYPE_RICE = 3;
const RESOURCE_TYPE_MOON = 4;
const RESOURCE_TYPE_SUN = 5;
const RESOURCES = [
    0,
   'silk',//RESOURCE_TYPE_SILK
   'pottery',//RESOURCE_TYPE_POTTERY
   'rice',//RESOURCE_TYPE_RICE
   'favor_total',//RESOURCE_TYPE_MOON
   'favor',//RESOURCE_TYPE_SUN
];

const SHORE_SPACE_BASE =                    1;
const SHORE_SPACE_IMPERIAL_MARKET =         2;
const SHORE_SPACE_STARTING_BUILDING_FOR_2 = 3;
const SHORE_SPACE_STARTING_BUILDING_FOR_3 = 4;

/////////////////////////////////////////////////////////
//          MEEPLES
/////////////////////////////////////////////////////////
const MEEPLE_TYPE_SHIP = 1;
const MEEPLE_TYPE_SHIP_ROYAL = 3;
const MEEPLE_TYPE_CLAN_MARKER = 2;

const MEEPLE_LOCATION_TILE = 'tile-';
CONST MEEPLE_LOCATION_INFLUENCE = 'i-';

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
const ST_PLAYER_TURN_BUILD = 21;

const ST_CONFIRM_CHOICES = 70;
const ST_CONFIRM_TURN = 71;

const ST_END_SCORING = 90;
const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;
 