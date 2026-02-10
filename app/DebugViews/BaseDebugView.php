<?php

namespace App\DebugViews;

use Illuminate\Support\Facades\DB;

abstract class BaseDebugView
{
    abstract public static function name(): string;

    abstract public static function definition(): string;

    abstract public static function cleanup(): void;

    public static function create(): void
    {
        DB::statement('CREATE OR REPLACE VIEW `'.static::name().'` AS '.static::definition());
    }
}
