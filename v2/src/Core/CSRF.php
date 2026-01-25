<?php
declare(strict_types=1);

namespace App\Core;

class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32;
    
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        Session::set(self::TOKEN_KEY, $token);
        return $token;
    }
    
    public static function token(): string
    {
        $token = Session::get(self::TOKEN_KEY);
        
        if ($token === null) {
            $token = self::generate();
        }
        
        return $token;
    }
    
    public static function verify(?string $token): bool
    {
        if ($token === null) {
            return false;
        }
        
        $sessionToken = Session::get(self::TOKEN_KEY);
        
        if ($sessionToken === null) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            htmlspecialchars(self::token())
        );
    }
    
    public static function meta(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars(self::token())
        );
    }
}
