<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Chat\Button;

enum Style: string
{
    case PRIMARY = 'primary';
    case SUCCESS = 'success';
    case DANGER = 'danger';
}
