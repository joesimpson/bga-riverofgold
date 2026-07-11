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
        public ?int $source = null,
        public ?int $dest = null,
        
        public ?ClientResourcesCount $res = null,
        
        public ?int $tileId = null,
    ) {

    }
}