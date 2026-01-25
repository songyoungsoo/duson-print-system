<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    private static string $basePath = '';
    private static array $shared = [];
    
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }
    
    public static function share(string $key, $value): void
    {
        self::$shared[$key] = $value;
    }
    
    public static function render(string $template, array $data = []): string
    {
        $templatePath = self::$basePath . '/' . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: $template");
        }
        
        $data = array_merge(self::$shared, $data);
        
        extract($data);
        
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
    
    public static function component(string $name, array $data = []): string
    {
        return self::render("components.$name", $data);
    }
    
    public static function layout(string $name, string $content, array $data = []): string
    {
        $data['content'] = $content;
        return self::render("layouts.$name", $data);
    }
    
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    public static function url(string $path): string
    {
        return '/v2/public' . $path;
    }
    
    public static function asset(string $path): string
    {
        return '/v2/public/assets/' . ltrim($path, '/');
    }
}
