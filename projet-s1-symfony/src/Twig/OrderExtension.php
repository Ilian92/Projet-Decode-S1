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
            new TwigFilter('order_status_badge_class', [$this, 'orderStatusBadgeClass']),
        ];
    }

    public function orderStatusLabel(?string $status): string
    {
        $enum = OrderStatus::tryFromString($status);

        return $enum?->label() ?? (string) $status;
    }

    public function orderStatusBadgeClass(?string $status): string
    {
        $enum = OrderStatus::tryFromString($status);

        return $enum?->badgeClass() ?? 'bg-gray-200 text-gray-900';
    }
}
