<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseWidget;
 
class CustomAccountWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    public static function canView(): bool
    {
        return false;
    }
} 