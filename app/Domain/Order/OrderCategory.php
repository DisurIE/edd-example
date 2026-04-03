<?php

namespace App\Domain\Order;

enum OrderCategory: string
{
    case STANDARD = 'standard';
    case HEAVY = 'heavy';
}
