<?php
namespace ROG\Helpers;

class ClientResourcesCount {
    public function __construct(
        public int $silk,
        public int $pottery,
        public int $rice,
        public int $money,
    ) {}
}