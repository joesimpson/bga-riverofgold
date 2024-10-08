<?php
 
const BGA_GAMESTATE_GAMEVERSION = 300;

/*
 * Game Constants
 */
 
 const DIE_FACES = [1,2,3,4,5,6];

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

 const NB_RIVER_SPACES = 14;
 const NB_MAX_MONEY = 25;
 const NB_MAX_RESOURCE = 6;
 
 const NB_MAX_INLFUENCE = 18;
 const STARTING_BOATS_SPACES = [1, 8];
 const NB_INLUENCE_ARTISAN = 2;
 const NB_INLUENCE_MERCHANT = 3;
 const NB_INLUENCE_NOBLE = 2;
 const NB_INLUENCE_FLOWER = 11;
 const NB_INLUENCE_VOID = 1;
 const NB_BUILDINGS_WATER = 3;
 const NB_CUSTOMERS_FOR_AIR = 3;
 const NB_CUSTOMERS_FOR_FIRE = 2;
 //RULE : empty space => 1 Koku
 const EMPTY_SPACE_REWARD = 1;

 //5 spaces between 2p
 const NB_SPACES_BETWEEN_2P_SCORINGTILE = 5;

 const NB_RESOURCES_FOR_1POINT_WITH_ARTISAN = 3;
 const NB_RESOURCES_FOR_1POINT_WITH_MERCHANT = 5;

 const NB_INLUENCE_MERCHANT_1 = 1;
 const NB_MONEY_FOR_SELLING_MERCHANT_4 = 5;
 const NB_RESOURCE_FOR_SELLING_MERCHANT_4 = 1;
 const NB_INLUENCE_NOBLE_3 = 1;
 const NB_POINTS_NOBLE_4 = 1;
 const NB_POINTS_NOBLE_6 = 1;

 const NB_POINTS_GOVERNOR = 3;
 const NB_POINTS_PRIESTESS = 3;
 const NB_POINTS_IRON_CRANE = 2;

 const NB_POINTS_FOR_GAME_END = 5;

 const ARTISAN_COST_REDUCTION = 2;
 
 const CUSTOMER_TYPE_ARTISAN =  1;
 const CUSTOMER_TYPE_ELDER =    2;
 const CUSTOMER_TYPE_MERCHANT = 3;
 const CUSTOMER_TYPE_MONK =     4;
 const CUSTOMER_TYPE_NOBLE =    5;
 const CUSTOMER_TYPES = [
   CUSTOMER_TYPE_ARTISAN ,
   CUSTOMER_TYPE_ELDER   ,
   CUSTOMER_TYPE_MERCHANT,
   CUSTOMER_TYPE_MONK    ,
   CUSTOMER_TYPE_NOBLE   ,
 ];
 
// 30 Customer cards
const CARD_ARTISAN_1 = 1 ;
const CARD_ARTISAN_2 = 2 ;
const CARD_ARTISAN_3 = 3 ;
const CARD_ARTISAN_4 = 4 ;
const CARD_ARTISAN_5 = 5 ;
const CARD_ARTISAN_6 = 6 ;
const CARD_ELDER_1 = 7 ;
const CARD_ELDER_2 = 8 ;
const CARD_ELDER_3 = 9 ;
const CARD_ELDER_4 = 10;
const CARD_ELDER_5 = 11;
const CARD_ELDER_6 = 12;
const CARD_MERCHANT_1 = 13;
const CARD_MERCHANT_2 = 14;
const CARD_MERCHANT_3 = 15;
const CARD_MERCHANT_4 = 16;
const CARD_MERCHANT_5 = 17;
const CARD_MERCHANT_6 = 18;
const CARD_MONK_1 = 19;
const CARD_MONK_2 = 20;
const CARD_MONK_3 = 21;
const CARD_MONK_4 = 22;
const CARD_MONK_5 = 23;
const CARD_MONK_6 = 24;
const CARD_NOBLE_1 = 25;
const CARD_NOBLE_2 = 26;
const CARD_NOBLE_3 = 27;
const CARD_NOBLE_4 = 28;
const CARD_NOBLE_5 = 29;
const CARD_NOBLE_6 = 30;

const MERCHANT_TYPES = [
   CARD_MERCHANT_1,
   CARD_MERCHANT_2,
   CARD_MERCHANT_3,
   CARD_MERCHANT_4,
   CARD_MERCHANT_5,
   CARD_MERCHANT_6,
];

const MONK_TYPE_OWN_BUILDING = 1;
const MONK_TYPE_OPPONENT_BUILDING = 2;
 
 const CARD_TYPE_CUSTOMER = 1;
 const CARD_TYPE_CLAN_PATRON = 2;

 const TILE_TYPE_SCORING = 1;
 const TILE_TYPE_BUILDING = 2;
 const TILE_TYPE_MASTERY_CARD = 3;
 
 const BUILDING_TYPE_PORT =     1;
 const BUILDING_TYPE_MARKET =   2;
 const BUILDING_TYPE_MANOR =    3;
 const BUILDING_TYPE_SHRINE =   4;
 const BUILDING_TYPES = [
   BUILDING_TYPE_PORT  ,
   BUILDING_TYPE_MARKET,
   BUILDING_TYPE_MANOR ,
   BUILDING_TYPE_SHRINE,
 ];
 
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
const TILE_LOCATION_DISCARD = 'discard';

const BUILDING_ROW_END = 4;
//Favor earned when building last tile of the row
const BUILDING_ROW_END_FAVOR = 1;

const CARD_LOCATION_DISCARD = 'discard';
 const CARD_LOCATION_DECK = 'deck';
 const CARD_LOCATION_DELIVERED = 'dd';
 const CARD_LOCATION_HAND = 'h';
 const CARD_LOCATION_WAIT_FOR_HAND = 'wait';
 //! Warning one clan patron will have more cards (3)
 const NB_CARDS_PER_PLAYER = 2;
 const NB_CARDS_FOR_YORITOMO = 3;
//Cost to use darling power
 const DARLING_FAVOR_COST = 1;

 const CARD_CLAN_LOCATION_DECK = 'clans_deck_';//To be followed by clan id
 const CARD_CLAN_LOCATION_DRAFT = 'clans_draft';
 const CARD_CLAN_LOCATION_DISCARD = 'clans_discard';
 const CARD_CLAN_LOCATION_ASSIGNED = 'clans_assigned';

 const PATRON_MASTER_ENGINEER = 1;
 const PATRON_TRADER          = 2;
 const PATRON_SON_OF_STORM    = 3;
 const PATRON_PRIESTESS       = 4;
 const PATRON_IRON_CRANE      = 5;
 const PATRON_DARLING         = 6;
 const PATRON_GOVERNOR        = 7;
 const PATRON_LADY            = 8;

const RESOURCE_TYPE_SILK = 1;
const RESOURCE_TYPE_POTTERY = 2;
const RESOURCE_TYPE_RICE = 3;
const RESOURCE_TYPE_MOON = 4;
const RESOURCE_TYPE_SUN = 5;
const RESOURCE_TYPE_MONEY = 6;
const BONUS_TYPE_POINTS = 20;
const BONUS_TYPE_CHOICE = 21;
const BONUS_TYPE_INFLUENCE = 22;
const BONUS_TYPE_DRAW = 23;
const BONUS_TYPE_UPGRADE_SHIP = 24;
const BONUS_TYPE_SECOND_MARKER_ON_BUILDING = 25;
const BONUS_TYPE_SECOND_MARKER_ON_OPPONENT = 26;
const BONUS_TYPE_MONEY_OR_GOOD = 27;
const BONUS_TYPE_SELL_GOODS = 28;
const BONUS_TYPE_REFILL_HAND = 29;
const BONUS_TYPE_MONEY_PER_PORT = 30;
const BONUS_TYPE_MONEY_PER_MANOR = 31;
const BONUS_TYPE_MONEY_PER_MARKET = 32;
const BONUS_TYPE_MONEY_PER_SHRINE = 33;
const BONUS_TYPE_MONEY_PER_CUSTOMER = 34;
const BONUS_TYPE_SET_DIE = 35;
const RESOURCES = [
    0,
   'silk',//RESOURCE_TYPE_SILK
   'pottery',//RESOURCE_TYPE_POTTERY
   'rice',//RESOURCE_TYPE_RICE
   'favor_total',//RESOURCE_TYPE_MOON
   'favor',//RESOURCE_TYPE_SUN
   'money',//RESOURCE_TYPE_MONEY
];

//array of resources and quantity to trade
const RESOURCES_TO_TRADE = [
   RESOURCE_TYPE_SILK =>    ['src'=>2,'dest'=>1] ,
   RESOURCE_TYPE_POTTERY => ['src'=>2,'dest'=>1] ,
   RESOURCE_TYPE_RICE =>    ['src'=>2,'dest'=>1] ,
   RESOURCE_TYPE_MONEY =>   ['src'=>5,'dest'=>0] ,
   RESOURCE_TYPE_SUN =>     ['src'=>0,'dest'=>1] ,
];
const RESOURCES_LIMIT = [
  RESOURCE_TYPE_SILK =>    NB_MAX_RESOURCE,
  RESOURCE_TYPE_POTTERY => NB_MAX_RESOURCE,
  RESOURCE_TYPE_RICE =>    NB_MAX_RESOURCE,
  RESOURCE_TYPE_MOON =>    NB_MAX_RESOURCE,
  RESOURCE_TYPE_MONEY =>   NB_MAX_MONEY,
];

const SHORE_SPACE_BASE =                    1;
const SHORE_SPACE_IMPERIAL_MARKET =         2;
const SHORE_SPACE_STARTING_BUILDING_FOR_2 = 3;
const SHORE_SPACE_STARTING_BUILDING_FOR_3 = 4;


/////////////////////////////////////////////////////////
//          INFLUENCE TRACK
/////////////////////////////////////////////////////////
const INFLUENCE_TRACK_REWARDS = [
   REGION_1 => [
      2 => ['n' => 1, 'type' => RESOURCE_TYPE_POTTERY], 
      5 => ['n' => 2, 'type' => RESOURCE_TYPE_MONEY], 
      9 => ['n' => 1, 'type' => RESOURCE_TYPE_SUN], 
      13=> ['n' => 3, 'type' => BONUS_TYPE_POINTS], 
      18=> ['n' => 1, 'type' => BONUS_TYPE_CHOICE], 
   ],
   REGION_2  => [
      2 => ['n' => 1, 'type' => RESOURCE_TYPE_RICE], 
      5 => ['n' => 2, 'type' => RESOURCE_TYPE_MONEY], 
      9 => ['n' => 1, 'type' => RESOURCE_TYPE_SUN], 
      13=> ['n' => 3, 'type' => BONUS_TYPE_POINTS], 
      18=> ['n' => 1, 'type' => BONUS_TYPE_CHOICE], 
   ],
   REGION_3  => [
      2 => ['n' => 1, 'type' => RESOURCE_TYPE_SILK], 
      5 => ['n' => 2, 'type' => RESOURCE_TYPE_MONEY], 
      9 => ['n' => 1, 'type' => RESOURCE_TYPE_SUN], 
      13=> ['n' => 3, 'type' => BONUS_TYPE_POINTS], 
      18=> ['n' => 1, 'type' => BONUS_TYPE_CHOICE], 
   ],
   REGION_4  => [
      2 => ['n' => 1, 'type' => RESOURCE_TYPE_POTTERY], 
      5 => ['n' => 2, 'type' => RESOURCE_TYPE_MONEY], 
      9 => ['n' => 1, 'type' => RESOURCE_TYPE_SUN], 
      13=> ['n' => 3, 'type' => BONUS_TYPE_POINTS], 
      18=> ['n' => 1, 'type' => BONUS_TYPE_CHOICE], 
   ],
   REGION_5  => [
      2 => ['n' => 1, 'type' => RESOURCE_TYPE_RICE], 
      5 => ['n' => 2, 'type' => RESOURCE_TYPE_MONEY], 
      9 => ['n' => 1, 'type' => RESOURCE_TYPE_SUN], 
      13=> ['n' => 3, 'type' => BONUS_TYPE_POINTS], 
      18=> ['n' => 1, 'type' => BONUS_TYPE_CHOICE], 
   ],
   REGION_6  => [
      2 => ['n' => 1, 'type' => RESOURCE_TYPE_SILK], 
      5 => ['n' => 2, 'type' => RESOURCE_TYPE_MONEY], 
      9 => ['n' => 1, 'type' => RESOURCE_TYPE_SUN], 
      13=> ['n' => 3, 'type' => BONUS_TYPE_POINTS], 
      18=> ['n' => 1, 'type' => BONUS_TYPE_CHOICE], 
   ],
 ];

/////////////////////////////////////////////////////////
//          MEEPLES
/////////////////////////////////////////////////////////
const MEEPLE_TYPE_SHIP = 1;
const MEEPLE_TYPE_SHIP_ROYAL = 3;
const MEEPLE_TYPE_CLAN_MARKER = 2;

const MEEPLE_LOCATION_TILE = 'tile-';//To be followed by tile id
CONST MEEPLE_LOCATION_INFLUENCE = 'i-';//To be followed by region number
const MEEPLE_LOCATION_RIVER = 'r';
CONST MEEPLE_LOCATION_ARTISAN = 'artisan-';//To be followed by region number
CONST MEEPLE_LOCATION_ELDER = 'elder-';//To be followed by region number
CONST MEEPLE_LOCATION_MERCHANT = 'merchant';

const CLAN_CRAB =    1;
const CLAN_MANTIS =  2;
const CLAN_CRANE =   3;
const CLAN_SCORPION = 4;
const CLANS_COLORS = [
   //blue
   '0000ff' => CLAN_CRAB,
   //green
   '008000' => CLAN_MANTIS,
   //white
   'ffffff' => CLAN_CRANE,
   //red
   'ff0000' => CLAN_SCORPION,
];


/////////////////////////////////////////////////////////
//          SCORING
/////////////////////////////////////////////////////////
const SCORING_INGAME = 1;
const SCORING_INFLUENCE = 2;
const SCORING_DELIVERED = 3;
const SCORING_CUSTOMERS = 4;

/*
 * Game options
 */ 
const OPTION_EXPANSION_CLANS = 110;
const OPTION_EXPANSION_CLANS_OFF = 0;
const OPTION_EXPANSION_CLANS_DRAFT = 1;
const OPTION_EXPANSION_CLANS_ALTERNATIVE = 2;
/*
 * User preferences
 */  
const PREF_PLAYER_PANEL_DETAILS = 100;
const PREF_PLAYER_PANEL_DETAILS_FULL = 1;
const PREF_PLAYER_PANEL_DETAILS_COMPACT = 2;

const PREF_UNDO_STYLE = 101;
const PREF_UNDO_STYLE_TEXT = 1;
const PREF_UNDO_STYLE_ICON = 2;

const PREF_PLAYER_PANEL_BACKGROUND = 102;
const PREF_PLAYER_PANEL_BACKGROUND_NONE = 1;
const PREF_PLAYER_PANEL_BACKGROUND_SYMBOL = 2;

const PREF_PLAYER_PANEL_BORDER = 103;
const PREF_PLAYER_PANEL_BORDER_OFF = 1;
const PREF_PLAYER_PANEL_BORDER_ON = 2;

const PREF_ANIMATION_SHIP_SELECTED = 104;
const PREF_ANIMATION_SHIP_SELECTED_OFF = 1;
const PREF_ANIMATION_SHIP_SELECTED_BOUNCE = 2;

const PREF_ANIMATION_LASTTURN_MESSAGE = 105;
const PREF_ANIMATION_LASTTURN_NORMAL = 1;
const PREF_ANIMATION_LASTTURN_BLINK = 2;

const PREF_ANIMATION_MOVING_RESOURCE = 106;
const PREF_ANIMATION_MOVING_RESOURCE_OFF = 1;
const PREF_ANIMATION_MOVING_RESOURCE_ON = 2;

const PREF_ANIMATION_MOVING_SCORE = 107;
const PREF_ANIMATION_MOVING_SCORE_OFF = 1;
const PREF_ANIMATION_MOVING_SCORE_ON = 2;

/*
 * State constants
 */
const ST_GAME_SETUP = 1;

const ST_CLAN_SELECTION = 3;
const ST_DRAFT_PLAYER = 4;
const ST_DRAFT_PLAYER_MULTIACTIVE = 5;
const ST_DRAFT_NEXT_PLAYER = 6;
const ST_PLAYER_SETUP = 8;
 
const ST_NEXT_TURN = 10;
const ST_BEFORE_TURN = 11;

const ST_PLAYER_TURN = 20;
const ST_PLAYER_TURN_BUILD = 21;
const ST_PLAYER_TURN_SAIL = 22;
const ST_PLAYER_TURN_DELIVER = 23;

const ST_BONUS_CHOICE = 24;
const ST_BONUS_CHOICE_RESOURCE = 25;
const ST_BONUS_UPGRADE_SHIP = 26;
const ST_BONUS_SECOND_MARKER_ON_BUILDING = 27;
const ST_BONUS_MONEY_OR_GOOD = 28;
const ST_BONUS_SELL_GOODS = 29;
const ST_BONUS_SET_DIE = 30;

const ST_PLAYER_TURN_TRADE = 40;
const ST_PLAYER_TURN_DIVINE_FAVOR = 41;

const ST_DISCARD_CARD = 60;

const ST_CONFIRM_CHOICES = 70;
const ST_CONFIRM_TURN = 71;
const ST_END_TURN = 80;

const ST_END_SCORING = 90;
const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;
 