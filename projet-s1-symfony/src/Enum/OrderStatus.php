<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING_RESTOCK = 'pending_restock';
    case PENDING_SHIPMENT = 'pending_shipment';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_RESTOCK => 'En attente de réappro',
            self::PENDING_SHIPMENT => 'En préparation',
            self::SHIPPED => 'Expédié',
            self::DELIVERED => 'Livré',
            self::CANCELLED => 'Annulée',
        };
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }
}
