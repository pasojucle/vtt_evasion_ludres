<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Member;

class MemberCertificat
{
    public function __construct(
        public Member $member,
        public string $content,
    ) {
    }
}
