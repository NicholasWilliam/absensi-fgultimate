<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['setting_key', 'setting_value'])]
class Setting extends Model
{
    public $timestamps = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('setting_key', $key)->value('setting_value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        Cache::forget("setting:{$key}");
    }
}
