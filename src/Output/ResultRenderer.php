<?php

declare(strict_types=1);

namespace AmezmoCli\Output;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

final class ResultRenderer
{
    /** @param array<string, string> $columns */
    public function render(OutputInterface $output, mixed $result, string $format, array $columns = []): void
    {
        $format = OutputFormat::tryFrom(strtolower($format)) ?? throw new \InvalidArgumentException('Output must be table, json, or yaml.');
        if ($format === OutputFormat::JSON) {
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            return;
        }
        if ($format === OutputFormat::YAML) {
            $output->write(Yaml::dump($result, 4, 2));
            return;
        }
        if (is_array($result) && !array_is_list($result)) {
            $output->write(Yaml::dump($result, 4, 2));
            return;
        }
        $rows = is_array($result) && array_is_list($result) ? $result : [$result];
        if ($rows === [[]]) {
            $output->writeln('No results.');
            return;
        }
        $headers = $columns !== [] ? array_keys($columns) : array_keys((array) $rows[0]);
        $table = new Table($output);
        $table->setHeaders($headers);
        foreach ($rows as $row) {
            $table->addRow(array_map(static fn($key) => is_scalar($row[$key] ?? null) ? (string) ($row[$key] ?? '') : json_encode($row[$key] ?? null), array_values($columns !== [] ? $columns : array_combine($headers, $headers))));
        }
        $table->render();
    }
}
