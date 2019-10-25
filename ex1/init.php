<?
use Zend\Db\Adapter\Adapter;
require_once './config.php';

/**
 * Класс для работы с таблицей TEST
 */
final class init{
  private const RESULT_VALS = [ 'normal'=>'normal', 
                                'illegal'=>'illegal',
                                'failed'=>'failed', 
                                'success'=>'success'];
  private $getStatement;
  private $qi,$fp;
  
  /**
   * Конструктор класса
   */
  public function __construct(){
    $this->adapter = new Adapter(getConfig());
    $adapter = $this->adapter;
    $this->qi = function ($name) use ($adapter) {
      return $adapter->platform->quoteIdentifier($name);
    };
    $this->fp = function ($name) use ($adapter) {
      return $adapter->driver->formatParameterName($name);
    };

    $qi = $this->qi;
    $fp = $this->fp;


    $getSql = "SELECT * FROM test WHERE result = 'normal' OR result = 'success';";
    
    $this->getStatement = $this->adapter->createStatement($getSql);

    $this->create();
    $this->fill();
  }
  
   /**
   * Создает таблицу test, содержащую 5 полей:
   * id, script_name, start_time, end_time, result
   * 
   * @return void
   */
  private function create(){
    $createSql = "CREATE TABLE IF NOT EXISTS test
    (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        script_name VARCHAR(25) NOT NULL,
        start_time INT NOT NULL,
        end_time INT NOT NULL,
        result ENUM('".self::RESULT_VALS['normal']."', 
                    '".self::RESULT_VALS['illegal']."', 
                    '".self::RESULT_VALS['failed']."', 
                    '".self::RESULT_VALS['success']."')
    )";
    $createStatement = $this->adapter->query($createSql);
    $createStatement->execute();
  }

  /**
   *  Заполняет таблицу test случайными данными
   * 
   * @return void
   */
  private function fill(){
    $sql = "INSERT INTO test ( 
      `script_name`, 
      `start_time`, 
      `end_time`, 
      `result` 
    ) VALUES ";
    $data = $this->randomData();
    $values = implode(', ',$data);
    $sql .= $values.';';
    $insertStatement = $this->adapter->query($sql);
    $insertStatement->execute();
  }


  /**
   *  Возвращает выборку из таблицы test, 
   *  соответсвующую критерию (result = normal and result = success)
   * 
   * @return ResultInterface результат выполнения select запроса
   */
  public function get(){
    return $this->getStatement->execute();
  }

  /**
   * Создет случайные данные, для заполнения базы данных
   * 
   * @return string[]
   */
  private function randomData(){
    $data = [];
    $count = mt_rand ( 5 , 100 );
    for($i = 0; $i<$count; $i++){
      $b = mt_rand ( 10 , 1000 );
      $data[] = 
        "( '". $this->randomString()."'"
        . ", '" . ($b - mt_rand( 5 , $b-1 ))."'"
        . ", '" . $b."'"
        . ", '". array_rand(self::RESULT_VALS) . "')";
    }
    return $data;
  }
  /**
   * Генерация случайной строки
   * 
   * @return string
   */
  private function randomString()
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < 10; $i++) {
      $randstring .= $characters[rand(0, strlen($characters)-1)];
    }
    return $randstring;
  }
}