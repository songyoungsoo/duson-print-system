 <?php                                                                                                                                                                           
  /**                                                                                                                                                                             
   * dsp114.co.kr 환경 설정                                                                                                                                                       
   */                                                                                                                                                                             
                                                                                                                                                                                  
  class EnvironmentDetector {                                                                                                                                                     
      private static $environment = null;                                                                                                                                         
      private static $config = null;                                                                                                                                              
                                                                                                                                                                                  
      public static function detectEnvironment() {                                                                                                                                
          if (self::$environment !== null) {                                                                                                                                      
              return self::$environment;                                                                                                                                          
          }                                                                                                                                                                       
                                                                                                                                                                                  
          $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';                                                                                                
                                                                                                                                                                                  
          if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {                                                                                     
              self::$environment = 'local';                                                                                                                                       
          } else {                                                                                                                                                                
              self::$environment = 'production';                                                                                                                                  
          }                                                                                                                                                                       
                                                                                                                                                                                  
          return self::$environment;                                                                                                                                              
      }                                                                                                                                                                           
                                                                                                                                                                                  
      public static function getDatabaseConfig() {                                                                                                                                
          if (self::$config !== null) {                                                                                                                                           
              return self::$config;                                                                                                                                               
          }                                                                                                                                                                       
                                                                                                                                                                                  
          // dsp114.co.kr DB 설정                                                                                                                                                 
          self::$config = [                                                                                                                                                       
              'host' => 'localhost',                                                                                                                                              
              'user' => 'dsp1830',                                                                                                                                                
              'password' => 't3zn?5R56',                                                                                                                                          
              'database' => 'dsp1830',                                                                                                                                            
              'charset' => 'utf8mb4',                                                                                                                                             
              'environment' => 'production',                                                                                                                                      
              'debug' => false                                                                                                                                                    
          ];                                                                                                                                                                      
                                                                                                                                                                                  
          return self::$config;                                                                                                                                                   
      }                                                                                                                                                                           
                                                                                                                                                                                  
      public static function isLocal() {                                                                                                                                          
          return self::detectEnvironment() === 'local';                                                                                                                           
      }                                                                                                                                                                           
                                                                                                                                                                                  
      public static function isProduction() {                                                                                                                                     
          return self::detectEnvironment() === 'production';                                                                                                                      
      }                                                                                                                                                                           
  }                                                                                                                                                                               
                                                                                                                                                                                  
  function get_db_config() {                                                                                                                                                      
      return EnvironmentDetector::getDatabaseConfig();                                                                                                                            
  }                                                                                                                                                                               
                                                                                                                                                                                  
  function is_local_environment() {                                                                                                                                               
      return EnvironmentDetector::isLocal();                                                                                                                                      
  }                                                                                                                                                                               
                                                                                                                                                                                  
  function is_production_environment() {                                                                                                                                          
      return EnvironmentDetector::isProduction();                                                                                                                                 
  }                                                                                                                                                                               
                                                                                                                                                                                  
  function get_current_environment() {                                                                                                                                            
      return EnvironmentDetector::detectEnvironment();                                                                                                                            
  }                                                                                                                                                                               
                                                                                                                                                                                  
  // 운영 환경: 오류 숨김                                                                                                                                                         
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);                                                                                                                                
  ini_set('display_errors', 0);                                                                                                                                                   
  ini_set('log_errors', 1);                                                                                                                                                       
  ?>                                                             