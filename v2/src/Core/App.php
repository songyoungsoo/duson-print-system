<?php
declare(strict_types=1);

namespace App\Core;

class App
{
    private static ?App $instance = null;
    private array $container = [];
    private Router $router;
    private Request $request;
    
    private function __construct()
    {
        $this->request = new Request();
        $this->router = new Router();
    }
    
    public static function getInstance(): App
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function router(): Router
    {
        return $this->router;
    }
    
    public function request(): Request
    {
        return $this->request;
    }
    
    public function set(string $key, $value): void
    {
        $this->container[$key] = $value;
    }
    
    public function get(string $key)
    {
        return $this->container[$key] ?? null;
    }
    
    public function run(): void
    {
        try {
            Session::start();
            $response = $this->router->dispatch($this->request);
            $response->send();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }
    
    private function handleException(\Throwable $e): void
    {
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
        
        if (getenv('APP_DEBUG') === 'true') {
            echo "<h1>Error</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            http_response_code(500);
            echo "서버 오류가 발생했습니다.";
        }
    }
}
