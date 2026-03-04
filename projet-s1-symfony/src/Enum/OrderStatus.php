<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PAYMENT_PENDING = 'payment_pending';
    case PENDING_RESTOCK = 'pending_restock';
    case PENDING_SHIPMENT = 'pending_shipment';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT_PENDING => 'Paiement en cours',
            self::PENDING_RESTOCK => 'En attente de réappro',
            self::PENDING_SHIPMENT => 'En préparation',
            self::SHIPPED => 'Expédié',
            self::DELIVERED => 'Livré',
            self::CANCELLED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    /**
     * Description complète de l'état de la commande pour l'affichage client.
     */
    public function description(): string
    {
        return match ($this) {
            self::PAYMENT_PENDING => 'Commande en attente de confirmation du paiement',
            self::PENDING_RESTOCK => 'Commande en attente de réapprovisionnement',
            self::PENDING_SHIPMENT => 'Commande en cours de traitement',
            self::SHIPPED => 'Commande expédiée',
            self::DELIVERED => 'Commande livrée',
            self::CANCELLED => 'Commande annulée',
            self::REFUNDED => 'Commande remboursée',
        };
    }

    /**
     * Tailwind CSS classes for status badge (bg-* text-*).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PAYMENT_PENDING => 'bg-slate-200 text-slate-800',
            self::PENDING_RESTOCK => 'bg-amber-200 text-amber-900',
            self::PENDING_SHIPMENT => 'bg-yellow-200 text-yellow-900',
            self::SHIPPED => 'bg-blue-200 text-blue-900',
            self::DELIVERED => 'bg-green-200 text-green-900',
            self::CANCELLED => 'bg-red-200 text-red-900',
            self::REFUNDED => 'bg-purple-200 text-purple-900',
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
