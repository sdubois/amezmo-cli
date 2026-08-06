<?php

declare(strict_types=1);

namespace AmezmoCli\Output;

enum OutputFormat: string
{
    case TABLE = 'table';
    case JSON = 'json';
    case YAML = 'yaml';
}
