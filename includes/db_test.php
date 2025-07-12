<?php
// Simple test database configuration for development
$session_dir = dirname(__DIR__) . '/tmp/sessions';
if (!is_dir($session_dir)) {
    mkdir($session_dir, 0755, true);
}

ini_set('session.save_path', $session_dir);
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);

// Create SQLite database for testing
$db_path = dirname(__DIR__) . '/tmp/test.db';
$conn = new SQLite3($db_path);

// Create tables if they don't exist
$conn->exec('CREATE TABLE IF NOT EXISTS productos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT,
    precio REAL,
    categoria TEXT,
    imagen TEXT,
    instagram TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$conn->exec('CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT,
    email TEXT,
    password TEXT,
    activo INTEGER DEFAULT 1,
    intentos_fallidos INTEGER DEFAULT 0,
    bloqueado_hasta DATETIME,
    ultimo_acceso DATETIME
)');

// Add sample products for testing
$conn->exec("INSERT OR IGNORE INTO productos (id, nombre, precio, categoria, imagen, instagram) VALUES 
(1, 'Collar de Perlas', 25000, 'collares', 'img/sample1.jpg', 'https://instagram.com/cfmjoyas'),
(2, 'Pulsera Dorada', 15000, 'pulseras', 'img/sample2.jpg', 'https://instagram.com/cfmjoyas'),
(3, 'Aretes de Plata', 12000, 'aretes', 'img/sample3.jpg', 'https://instagram.com/cfmjoyas'),
(4, 'Cerámica Artesanal', 30000, 'ceramicas', 'img/sample4.jpg', 'https://instagram.com/cfmjoyas')");

// Utility functions
function limpiar_input($data) {
    return htmlspecialchars(trim($data));
}

function validar_codigo_acceso($codigo) {
    $codigos_validos = ['CFM2025', 'JOYAS2025', 'ADMIN2025'];
    return in_array($codigo, $codigos_validos);
}

function verificar_intentos_login($email) {
    return true; // Always allow for testing
}

function registrar_intento_fallido($email) {
    // No-op for testing
}

function limpiar_intentos($email) {
    // No-op for testing
}

function createAuthCookie($user_id, $user_name, $user_email) {
    return true; // Simple implementation for testing
}

function verifyAuthCookie() {
    return false; // No auth for testing
}

function clearAuthCookie() {
    // No-op for testing
}

function iniciar_sesion_segura() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// For compatibility with mysqli style queries
class SQLiteWrapper {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function query($sql) {
        $result = $this->db->query($sql);
        return new SQLiteResultWrapper($result);
    }
    
    public function prepare($sql) {
        return new SQLitePreparedWrapper($this->db, $sql);
    }
}

class SQLiteResultWrapper {
    private $result;
    public $num_rows = 0;
    
    public function __construct($result) {
        $this->result = $result;
        // Count rows
        if ($result) {
            $data = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $data[] = $row;
            }
            $this->num_rows = count($data);
            $this->data = $data;
            $this->index = 0;
        }
    }
    
    public function fetch_assoc() {
        if (isset($this->data[$this->index])) {
            return $this->data[$this->index++];
        }
        return null;
    }
}

class SQLitePreparedWrapper {
    private $db;
    private $sql;
    
    public function __construct($db, $sql) {
        $this->db = $db;
        $this->sql = $sql;
    }
    
    public function bind_param($types, ...$params) {
        // Simple implementation for testing
        $this->params = $params;
    }
    
    public function execute() {
        if (isset($this->params)) {
            $sql = $this->sql;
            foreach ($this->params as $param) {
                $sql = preg_replace('/\?/', "'" . SQLite3::escapeString($param) . "'", $sql, 1);
            }
        } else {
            $sql = $this->sql;
        }
        $this->result = $this->db->query($sql);
        return true;
    }
    
    public function get_result() {
        return new SQLiteResultWrapper($this->result);
    }
}

$conn = new SQLiteWrapper($conn);
?>