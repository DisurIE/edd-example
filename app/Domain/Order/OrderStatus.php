<?php

namespace App\Domain\Order;

enum OrderStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case FULFILLED = 'fulfilled';
}
