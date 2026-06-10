<?php
namespace ROG\Helpers;

trait EnumUtilsTrait {
  public static function names(): array
  {
    return array_column(self::cases(), 'name');
  }
  public static function values(): array
  {
    return array_column(self::cases(), 'value');
  }
  public static function ui(): array
  {
    return array_combine(self::names(),self::values());
  }
}