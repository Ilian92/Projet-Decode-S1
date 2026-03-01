<?php

namespace App\Twig;

use App\Enum\OrderStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class OrderExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('order_status_label', [$this, 'orderStatusLabel']),
        ];
    }

    public function orderStatusLabel(?string $status): string
    {
        $enum = OrderStatus::tryFromString($status);

        return $enum?->label() ?? (string) $status;
    }
}
