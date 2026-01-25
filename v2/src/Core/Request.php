<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $files;
    private array $cookies;
    private array $json;
    
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->json = $this->parseJsonBody();
    }
    
    private function parseJsonBody(): array
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $rawBody = file_get_contents('php://input');
            if ($rawBody) {
                $decoded = json_decode($rawBody, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        return [];
    }
    
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }
    
    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return $path ?: '/';
    }
    
    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }
    
    public function post(string $key, $default = null)
    {
        return $this->json[$key] ?? $this->post[$key] ?? $default;
    }
    
    public function input(string $key, $default = null)
    {
        return $this->json[$key] ?? $this->post[$key] ?? $this->get[$key] ?? $default;
    }
    
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json);
    }
    
    public function json(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->json;
        }
        return $this->json[$key] ?? $default;
    }
    
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }
    
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }
    
    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? null;
    }
    
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
    
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }
    
    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? '0.0.0.0';
    }
    
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}
