<?php
namespace ROG\Helpers;

class ClientCardResources {
    public function __construct(
        public int $cardId,
        public int $silk,
        public int $pottery,
        public int $rice,
    ) {}
}