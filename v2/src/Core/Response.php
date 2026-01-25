<?php
declare(strict_types=1);

namespace App\Core;

class Response
{
    private string $content = '';
    private int $statusCode = 200;
    private array $headers = [];
    
    public function __construct(string $content = '', int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
    }
    
    public static function html(string $content, int $statusCode = 200): self
    {
        $response = new self($content, $statusCode);
        $response->header('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }
    
    public static function json(array $data, int $statusCode = 200): self
    {
        $response = new self(json_encode($data, JSON_UNESCAPED_UNICODE), $statusCode);
        $response->header('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }
    
    public static function redirect(string $url, int $statusCode = 302): self
    {
        $response = new self('', $statusCode);
        $response->header('Location', $url);
        return $response;
    }
    
    public static function notFound(string $message = '페이지를 찾을 수 없습니다.'): self
    {
        return self::html($message, 404);
    }
    
    public static function error(string $message = '서버 오류가 발생했습니다.'): self
    {
        return self::html($message, 500);
    }
    
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function cookie(
        string $name, 
        string $value, 
        int $expires = 0, 
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    ): self {
        setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
        return $this;
    }
    
    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        echo $this->content;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
