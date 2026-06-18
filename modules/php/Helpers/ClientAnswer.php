<?php
namespace ROG\Helpers;

use ROG\Models\BEFORE_ACTION;

class ClientAnswer {
    public function __construct(
        public int $cardId,
        public ?int $markerId,
        /**
         * @see enum BEFORE_ACTION 
         **/
        public string $action,
        public ?int $source,
        public ?int $dest,
    ) {}
}