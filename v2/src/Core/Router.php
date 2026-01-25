<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';
    
    public function get(string $path,  $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }
    
    public function post(string $path,  $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }
    
    public function put(string $path,  $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete(string $path,  $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }
    
    public function group(string $prefix, callable $callback): self
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $previousPrefix . $prefix;
        $callback($this);
        $this->prefix = $previousPrefix;
        return $this;
    }
    
    public function middleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
    
    private function addRoute(string $method, string $path,  $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->routes[$method][$fullPath] = [
            'handler' => $handler,
            'middlewares' => $this->middlewares,
        ];
        return $this;
    }
    
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();
        
        $path = preg_replace('#^/v2/public#', '', $path);
        if (empty($path)) {
            $path = '/';
        }
        
        foreach ($this->routes[$method] ?? [] as $route => $config) {
            $params = $this->match($route, $path);
            if ($params !== null) {
                return $this->executeHandler($config['handler'], $request, $params);
            }
        }
        
        return Response::notFound();
    }
    
    private function match(string $route, string $path): ?array
    {
        // {param} 패턴을 정규식으로 변환
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = "#^{$pattern}$#";
        
        if (preg_match($pattern, $path, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }
        
        return null;
    }
    
    private function executeHandler($handler, Request $request, array $params): Response
    {
        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            return $controller->$method($request, $params);
        }
        
        if (is_callable($handler)) {
            return $handler($request, $params);
        }
        
        throw new \RuntimeException('Invalid route handler');
    }
}
