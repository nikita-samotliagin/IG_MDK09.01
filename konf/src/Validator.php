<?php
declare(strict_types=1);

namespace Konf;

final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function required(string $field, string $message): self
    {
        if (($this->data[$field] ?? '') === '') {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    public function regex(string $field, string $pattern, string $message): self
    {
        $v = $this->data[$field] ?? '';
        if ($v !== '' && !preg_match($pattern, $v)) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    public function minLen(string $field, int $min, string $message): self
    {
        $v = $this->data[$field] ?? '';
        if ($v !== '' && mb_strlen($v) < $min) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    public function check(string $field, bool $ok, string $message): self
    {
        if (!$ok) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function ok(): bool
    {
        return $this->errors === [];
    }

    public const LOGIN = '/^[A-Za-z0-9]{6,}$/';
    public const FIO   = '/^[А-Яа-яЁё\s-]+$/u';
    public const PHONE = '/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/';
}
