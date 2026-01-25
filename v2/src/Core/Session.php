<?php
declare(strict_types=1);

namespace App\Core;

class Session
{
    private static bool $started = false;
    
    public static function start(): void
    {
        if (self::$started) {
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        self::$started = true;
    }
    
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    public static function clear(): void
    {
        $_SESSION = [];
    }
    
    public static function destroy(): void
    {
        self::clear();
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        self::$started = false;
    }
    
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
    
    public static function id(): string
    {
        return session_id();
    }
    
    public static function flash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
    
    public static function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }
}
