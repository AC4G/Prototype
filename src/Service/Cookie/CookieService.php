<?php declare(strict_types=1);

namespace App\Service\Cookie;

use DateTime;
use Symfony\Component\HttpFoundation\Cookie;

class CookieService
{
    public function __invoke(
        string $name,
        string $value,
        DateTime $expire
    )
    {
        return Cookie::create($name)
            ->withValue($value)
            ->withExpires($expire)
        ;
    }
}