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
            return rtrim($appData ?? (string) getenv('APPDATA'), '\\/') . DIRECTORY_SEPARATOR . 'amezmo-cli' . DIRECTORY_SEPARATOR . 'config.json';
        }
        if ($os === 'Darwin') {
            return $home . '/Library/Application Support/amezmo-cli/config.json';
        }
        return rtrim($xdg ?? (string) getenv('XDG_CONFIG_HOME'), '/') . ($xdg ? '' : $home . '/.config') . '/amezmo-cli/config.json';
    }
}
