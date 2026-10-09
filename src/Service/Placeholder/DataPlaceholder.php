<?php

declare(strict_types=1);

namespace App\Service\Placeholder;

 readonly class DataPlaceholder
 {
     /**
      * @param array<string, string> $values
      */
     public function __construct(
         public array $values
     ) {
     }
 }
