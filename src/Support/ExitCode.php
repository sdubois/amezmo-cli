<?php

declare(strict_types=1);

namespace AmezmoCli\Support;

final class ExitCode
{
    public const SUCCESS = 0;
    public const ERROR = 1;
    public const USAGE = 2;
    public const AUTH = 3;
    public const NOT_FOUND = 4;
    public const API = 5;
    public const VALIDATION = 6;
}
