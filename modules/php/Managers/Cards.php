<?php

namespace ROG\Managers;

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
      ];
    };
    return [
      // 30 unique CUSTOMER cards
      1 => $f([CUSTOMER_TYPE_ARTISAN, REGION_1]), 
      2 => $f([CUSTOMER_TYPE_ARTISAN, REGION_2]), 
      3 => $f([CUSTOMER_TYPE_ARTISAN, REGION_3]), 
      4 => $f([CUSTOMER_TYPE_ARTISAN, REGION_4]), 
      5 => $f([CUSTOMER_TYPE_ARTISAN, REGION_5]), 
      6 => $f([CUSTOMER_TYPE_ARTISAN, REGION_6]), 
      7 => $f([CUSTOMER_TYPE_ELDER, REGION_1]), 
      8 => $f([CUSTOMER_TYPE_ELDER, REGION_2]), 
      9 => $f([CUSTOMER_TYPE_ELDER, REGION_3]), 
      10 => $f([CUSTOMER_TYPE_ELDER, REGION_4]), 
      11 => $f([CUSTOMER_TYPE_ELDER, REGION_5]), 
      12 => $f([CUSTOMER_TYPE_ELDER, REGION_6]), 
      13 => $f([CUSTOMER_TYPE_MERCHANT, REGION_1]), 
      14 => $f([CUSTOMER_TYPE_MERCHANT, REGION_2]), 
      15 => $f([CUSTOMER_TYPE_MERCHANT, REGION_3]), 
      16 => $f([CUSTOMER_TYPE_MERCHANT, REGION_4]), 
      17 => $f([CUSTOMER_TYPE_MERCHANT, REGION_5]), 
      18 => $f([CUSTOMER_TYPE_MERCHANT, REGION_6]), 
      19 => $f([CUSTOMER_TYPE_MONK, REGION_1]), 
      20 => $f([CUSTOMER_TYPE_MONK, REGION_2]), 
      21 => $f([CUSTOMER_TYPE_MONK, REGION_3]), 
      22 => $f([CUSTOMER_TYPE_MONK, REGION_4]), 
      23 => $f([CUSTOMER_TYPE_MONK, REGION_5]), 
      24 => $f([CUSTOMER_TYPE_MONK, REGION_6]), 
      25 => $f([CUSTOMER_TYPE_NOBLE, REGION_1]), 
      26 => $f([CUSTOMER_TYPE_NOBLE, REGION_2]), 
      27 => $f([CUSTOMER_TYPE_NOBLE, REGION_3]), 
      28 => $f([CUSTOMER_TYPE_NOBLE, REGION_4]), 
      29 => $f([CUSTOMER_TYPE_NOBLE, REGION_5]), 
      30 => $f([CUSTOMER_TYPE_NOBLE, REGION_6]), 
    ];
  }
  
}
