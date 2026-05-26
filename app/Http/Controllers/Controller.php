<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function resolveDate($item): \Carbon\Carbon
    {
        $date = $item->date;
        if ($date->format('H:i:s') === '00:00:00' && $item->created_at) {
            return $date->copy()->setTime(
                $item->created_at->hour,
                $item->created_at->minute,
                $item->created_at->second
            );
        }
        return $date;
    }
}
