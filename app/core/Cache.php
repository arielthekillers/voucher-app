<?php
/**
 * Cache Singleton Class
 * Berfungsi untuk menghubungkan aplikasi ke Redis Server jika tersedia.
 * Jika REDIS_HOST tidak diset (misal di lokal), class ini akan menggunakan mode 'Dummy' 
 * (tidak melakukan cache) agar aplikasi tidak error.
 */
class Cache {
    private static $instance = null;
    private $redis = null;
    private $enabled = false;

    private function __construct() {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT') ?: 6379;

        if ($host && class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                // Gunakan timeout singkat (2 detik) agar tidak menggantung jika server Redis mati
                if ($this->redis->connect($host, $port, 2.0)) {
                    $this->enabled = true;
                    $this->initSession($host, $port);
                }
            } catch (Exception $e) {
                // Redis gagal koneksi, tetap lanjutkan dalam mode dummy
                error_log("Redis Connection Failed: " . $e->getMessage());
                $this->enabled = false;
            }
        }
    }

    /**
     * Otomatis mengarahkan Session PHP ke Redis jika terkoneksi.
     * Ini adalah best practice untuk menghindari session hilang saat deploy dengan Docker.
     */
    private function initSession($host, $port) {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', "tcp://$host:$port");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    /**
     * Menyimpan data ke Cache (dengan opsi expired time dalam detik)
     */
    public static function set($key, $value, $ttl = 3600) {
        $cache = self::getInstance();
        if ($cache->enabled) {
            // Serialize data supaya bisa menyimpan array/object
            $cache->redis->setex($key, $ttl, serialize($value));
            return true;
        }
        return false; // Mode lokal/dummy
    }

    /**
     * Mengambil data dari Cache
     */
    public static function get($key) {
        $cache = self::getInstance();
        if ($cache->enabled) {
            $data = $cache->redis->get($key);
            if ($data !== false) {
                return unserialize($data);
            }
        }
        return false; // Mode lokal/dummy (selalu anggap tidak ada cache)
    }

    /**
     * Menghapus cache berdasarkan key
     */
    public static function delete($key) {
        $cache = self::getInstance();
        if ($cache->enabled) {
            $cache->redis->del($key);
            return true;
        }
        return false;
    }

    /**
     * Mengecek apakah Cache Engine aktif
     */
    public static function isEnabled() {
        return self::getInstance()->enabled;
    }
}
