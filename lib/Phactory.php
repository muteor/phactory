<?
require_once('Phactory/Blueprint.php');
require_once('Phactory/Row.php');

class Phactory {
    protected static $_tables = array();
    protected static $_pdo;

    private function __construct() {}

    public static function setConnection($pdo) {
        self::$_pdo = $pdo;
    }

    public static function getConnection() {
        return self::$_pdo;
    }

    public static function define($table, $defaults) {
        self::$_tables[$table] = new Phactory_Blueprint($table, $defaults);
    }

    public static function create($table, $overrides = array()) {
        if(! ($blueprint = self::$_tables[$table]) ) {
            throw new Exception("No table defined for '$table'");
        }
            
        $row = $blueprint->create();

        foreach($overrides as $field => $value) {
            $row[$field] = $value;
        }
     
        $row->save();

        return $row;
    }

    public static function get($table, $byColumn) {
        $column = array_keys($byColumn);
        $column = $column[0];
        $value = $byColumn[$column];

        $sql = "SELECT *
                FROM `$table`
                WHERE `$column` = :value";
        $stmt = self::$_pdo->prepare($sql);
        $stmt->execute(array(':value' => $value));
        $result = $stmt->fetch();
        
        if(false === $result) {
            return null;
        }

        return new Phactory_Row($table, $result);
    }

    public static function teardown() {
        foreach(self::$_tables as $table => $blueprint) {
            self::_truncate($table);
        }
    }

    protected static function _truncate($table) {
        $sql = "TRUNCATE :table";
        $stmt = self::$_pdo->prepare($sql);
        return $tmt->execute(array(':table' => $table));
    }
}
