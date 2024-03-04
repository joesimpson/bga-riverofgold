<?php

namespace ROG\Managers;

use ROG\Core\Notifications;
use ROG\Helpers\Collection;

/* Class to manage all the cards */

class Cards extends \ROG\Helpers\Pieces
{
  protected static $table = 'cards';
  protected static $prefix = 'card_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['player_id', 'type'];

  protected static function cast($row)
  {
    $data = self::getCards()[$row['type']];
    return new \ROG\Models\Card($row, $data);
  }

  /**
   * @param int $currentPlayerId Id of current player loading the game
   * @return array all cards visible by this player
   */
  public static function getUiData($currentPlayerId)
  {
    $privateCards = self::getPlayerHandOrders($currentPlayerId);

    return self::getInLocation(CARD_LOCATION_DELIVERED)
      ->merge($privateCards)
      ->map(function ($card) {
        return $card->getUiData();
      })
      ->toArray();
  } 
 
  /**
   * @param int $pId
   * @param string $location
   * @return int number of ALL CARDS owned by that player and in that $location,
   *   or ALL CARDS owned by that player if location not given
   */
  public static function countPlayerCards($pId, $location = null)
  {
    return self::getFilteredQuery($pId, $location)->count();
  }
  
  /**
   * Return all HAND cards of this player
   * @param int $pId
   * @return Collection
   */
  public static function getPlayerHandOrders($pId)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_HAND)->get();
  }
  public static function refreshHands($players)
  {
    foreach ($players as $pid => $player) {
      Notifications::refreshHand($pid,Cards::getPlayerHandOrders($pid));
    }
  }
  /**
   * Return all delivered cards of this player
   * @param int $pId
   * @return Collection
   */
  public static function getPlayerDeliveredOrders($pId)
  {
    return self::getFilteredQuery($pId, CARD_LOCATION_DELIVERED)->get();
  }

  /* Creation of the cards */
  public static function setupNewGame($players, $options)
  {
    $cards = [];

    foreach (self::getCards() as $type => $card) {
      $cards[] = [
        'location' => CARD_LOCATION_DECK,
        'type' => $type,
      ];
    }

    self::create($cards);
    self::shuffle(CARD_LOCATION_DECK);
  }

  /**
   * @return array of all the different types of Customer Cards
   */
  public static function getCards()
  { 
    return self::getCustomerCards();
  }
  /**
   * @return array of all the different types of Customer Cards
   */
  public static function getCustomerCards()
  {
    $f = function ($t) {
      return [
        'customerType' => $t[0],
        'region' => $t[1],
        'title' => $t[2],
        'desc' => $t[3],
      ];
    };
    return [
      // 30 unique CUSTOMER cards
      1 => $f([CUSTOMER_TYPE_ARTISAN, REGION_1,  clienttranslate('Artisan')   , clienttranslate('Artisan 1'),  ]), 
      2 => $f([CUSTOMER_TYPE_ARTISAN, REGION_2,  clienttranslate('Artisan')   , clienttranslate('Artisan 2'),  ]), 
      3 => $f([CUSTOMER_TYPE_ARTISAN, REGION_3,  clienttranslate('Artisan')   , clienttranslate('Artisan 3'),  ]), 
      4 => $f([CUSTOMER_TYPE_ARTISAN, REGION_4,  clienttranslate('Artisan')   , clienttranslate('Artisan 4'),  ]), 
      5 => $f([CUSTOMER_TYPE_ARTISAN, REGION_5,  clienttranslate('Artisan')   , clienttranslate('Artisan 5'),  ]), 
      6 => $f([CUSTOMER_TYPE_ARTISAN, REGION_6,  clienttranslate('Artisan')   , clienttranslate('Artisan 6'),  ]), 
      7 => $f([CUSTOMER_TYPE_ELDER, REGION_1 ,   clienttranslate('Elder')   , clienttranslate('Elder 1'),  ]), 
      8 => $f([CUSTOMER_TYPE_ELDER, REGION_2 ,   clienttranslate('Elder')   , clienttranslate('Elder 2'),  ]), 
      9 => $f([CUSTOMER_TYPE_ELDER, REGION_3 ,   clienttranslate('Elder')   , clienttranslate('Elder 3'),  ]), 
      10 => $f([CUSTOMER_TYPE_ELDER, REGION_4,   clienttranslate('Elder')   , clienttranslate('Elder 4'),  ]), 
      11 => $f([CUSTOMER_TYPE_ELDER, REGION_5,   clienttranslate('Elder')   , clienttranslate('Elder 5'),  ]), 
      12 => $f([CUSTOMER_TYPE_ELDER, REGION_6,   clienttranslate('Elder')   , clienttranslate('Elder 6'),  ]), 
      13 => $f([CUSTOMER_TYPE_MERCHANT, REGION_1,clienttranslate('Merchant')   , clienttranslate('Merchant 1'),]), 
      14 => $f([CUSTOMER_TYPE_MERCHANT, REGION_2,clienttranslate('Merchant')   , clienttranslate('Merchant 2'),]), 
      15 => $f([CUSTOMER_TYPE_MERCHANT, REGION_3,clienttranslate('Merchant')   , clienttranslate('Merchant 3'),]), 
      16 => $f([CUSTOMER_TYPE_MERCHANT, REGION_4,clienttranslate('Merchant')   , clienttranslate('Merchant 4'),]), 
      17 => $f([CUSTOMER_TYPE_MERCHANT, REGION_5,clienttranslate('Merchant')   , clienttranslate('Merchant 5'),]), 
      18 => $f([CUSTOMER_TYPE_MERCHANT, REGION_6,clienttranslate('Merchant')   , clienttranslate('Merchant 6'),]), 
      19 => $f([CUSTOMER_TYPE_MONK, REGION_1,    clienttranslate('Monk')   , clienttranslate('Monk 1'),]), 
      20 => $f([CUSTOMER_TYPE_MONK, REGION_2,    clienttranslate('Monk')   , clienttranslate('Monk 2'),]), 
      21 => $f([CUSTOMER_TYPE_MONK, REGION_3,    clienttranslate('Monk')   , clienttranslate('Monk 3'),]), 
      22 => $f([CUSTOMER_TYPE_MONK, REGION_4,    clienttranslate('Monk')   , clienttranslate('Monk 4'),]), 
      23 => $f([CUSTOMER_TYPE_MONK, REGION_5,    clienttranslate('Monk')   , clienttranslate('Monk 5'),]), 
      24 => $f([CUSTOMER_TYPE_MONK, REGION_6,    clienttranslate('Monk')   , clienttranslate('Monk 6'),]), 
      25 => $f([CUSTOMER_TYPE_NOBLE, REGION_1,   clienttranslate('Noble')   , clienttranslate('Noble 1'),]), 
      26 => $f([CUSTOMER_TYPE_NOBLE, REGION_2,   clienttranslate('Noble')   , clienttranslate('Noble 2'),]), 
      27 => $f([CUSTOMER_TYPE_NOBLE, REGION_3,   clienttranslate('Noble')   , clienttranslate('Noble 3'),]), 
      28 => $f([CUSTOMER_TYPE_NOBLE, REGION_4,   clienttranslate('Noble')   , clienttranslate('Noble 4'),]), 
      29 => $f([CUSTOMER_TYPE_NOBLE, REGION_5,   clienttranslate('Noble')   , clienttranslate('Noble 5'),]), 
      30 => $f([CUSTOMER_TYPE_NOBLE, REGION_6,   clienttranslate('Noble')   , clienttranslate('Noble 6'),]), 
    ];
  }
  
}
