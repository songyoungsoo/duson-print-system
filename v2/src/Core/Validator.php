<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public static function make(array $data): self
    {
        return new self($data);
    }
    
    public function required(string $field, string $message = ''): self
    {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field][] = $message ?: "$field 필드는 필수입니다.";
        }
        return $this;
    }
    
    public function email(string $field, string $message = ''): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?: "올바른 이메일 형식이 아닙니다.";
        }
        return $this;
    }
    
    public function min(string $field, int $length, string $message = ''): self
    {
        if (isset($this->data[$field]) && mb_strlen((string)$this->data[$field]) < $length) {
            $this->errors[$field][] = $message ?: "$field 필드는 최소 {$length}자 이상이어야 합니다.";
        }
        return $this;
    }
    
    public function max(string $field, int $length, string $message = ''): self
    {
        if (isset($this->data[$field]) && mb_strlen((string)$this->data[$field]) > $length) {
            $this->errors[$field][] = $message ?: "$field 필드는 최대 {$length}자까지 가능합니다.";
        }
        return $this;
    }
    
    public function numeric(string $field, string $message = ''): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?: "$field 필드는 숫자여야 합니다.";
        }
        return $this;
    }
    
    public function phone(string $field, string $message = ''): self
    {
        if (isset($this->data[$field])) {
            $phone = preg_replace('/[^0-9]/', '', $this->data[$field]);
            if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
                $this->errors[$field][] = $message ?: "올바른 전화번호 형식이 아닙니다.";
            }
        }
        return $this;
    }
    
    public function in(string $field, array $values, string $message = ''): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field][] = $message ?: "$field 필드의 값이 올바르지 않습니다.";
        }
        return $this;
    }
    
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    public function passes(): bool
    {
        return empty($this->errors);
    }
    
    public function errors(): array
    {
        return $this->errors;
    }
    
    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}
