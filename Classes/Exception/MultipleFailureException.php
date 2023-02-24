<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Exception;

use Exception;

class MultipleFailureException extends Exception
{
    /**
     * @var array<int, array{exception: Exception, item: mixed}>
     */
    protected array $exceptions = [];

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @param mixed $item
     */
    public function addException(Exception $exception, $item): void
    {
        $this->exceptions[] = [
            'exception' => $exception,
            'item' => $item,
        ];
    }
}
