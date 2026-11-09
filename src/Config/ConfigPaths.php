<?php

declare(strict_types=1);

namespace AmezmoCli\Config;

final class ConfigPaths
{
    public static function file(?string $home = null, ?string $os = null, ?string $xdg = null, ?string $appData = null): string
    {
        $home ??= (string) (getenv('HOME') ?: getenv('USERPROFILE') ?: '.');
        $os ??= PHP_OS_FAMILY;
        if ($os === 'Windows') {
            return rtrim($appData ?? (string) getenv('APPDATA'), '\\/') . '\\amezmo-cli\\config.json';
        }
        if ($os === 'Darwin') {
            return $home . '/Library/Application Support/amezmo-cli/config.json';
        }
        $xdgHome = $xdg ?? (($value = getenv('XDG_CONFIG_HOME')) !== false && $value !== '' ? $value : null);
        if ($xdgHome !== null) {
            return rtrim($xdgHome, '/') . '/amezmo-cli/config.json';
        }
        return $home . '/.config/amezmo-cli/config.json';
    }
}
