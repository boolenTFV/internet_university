<?
  /**
   * Скрипт закачивания страницы http://www.bills.ru, из страницы извлечь даты, заголовки, ссылки в блоке "события на долговом рынке". 
   * Данные сохраняются в таблицу bills_ru_events, имеющей такую структуру:
   * id, date, title, url
   */

  /**
   * Функция, которая получает содержимое 
   * удаленного документа, по его url
   * 
   * @param string $url Url адрес удаленного объекта.
   * 
   * @return string 
   */
  function getRemote($url)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
  }

  /**
   * Форматирование русской даты
   * 
   * @param string $date Строка содержащая дату месяцем на русском языке.
   * @param string $fromFormat Исходный формат даты.
   * @param string $toFormat Строка формата. К этому формату будет приведена дата.
   * 
   * @return string
   */
  function formatDate( $date, $fromFormat, $toFormat ) {
    $ru_months = array( 'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек' );
    $en_months = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
    $date = str_replace( $ru_months, $en_months, $date );
    return DateTime::createFromFormat(  $fromFormat, $date )->format( $toFormat );
  }

  /**
   * Парсит html документ, и возвращает содержимое таблицы "события на долговом рынке"
   * 
   * @param string $html Содержимое html документа.
   * 
   * @return string[][]
   */
  function parse($html){
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $table = $dom->getElementById('bizon_api_news_list');
    $rows = $table->childNodes;
    $result = [];
    foreach($rows as $row){
      $cells = $row->childNodes;
      if($cells !=null){
        $date = trim( $cells->item(1)->nodeValue );
        $date = formatDate($date, 'j M Y', 'Y-m-d H:i:s');
        $result[]=[
          "date"=> $date,
          "title"=> trim( $cells->item(3)->childNodes->item(1)
                                ->nodeValue ),
          "url"=> trim( $cells->item(3)->childNodes->item(1)
                              ->getAttribute('href') )
        ];
      }
    }
    return $result;
  }

  /**
   * Построение запроса на вставку в таблицу bills_ru_events.
   * 
   * @param string[][] $data Массив сущностей для вставки.
   * 
   * @return string
   */
  function buildInsertQuery($data){
    $query = "INSERT INTO bills_ru_events (date, title ,url) VALUES ";

    $placeholder = implode(', ', array_fill(0,count($data[0]),'?'));
    $placeholders = implode(', ', array_fill(0,count($data),"($placeholder)"));
    $query .= $placeholders.';';
    return $query;
  }

  /**
   * Подключение с помощью pdo драйвера. Возврощает pdo объект.
   * 
   * @return mixed
   */
  function connection(){
    $host = '127.0.0.1';
    $db   = 'test';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host=$host;dbname=$db";

    try {
      $pdo = new PDO($dsn, $user, $pass);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }   
    return $pdo;
  }
  function saveInDb($data){
    $pdo = connection();
    //уплощение массива
    $flatData = (object) array('aFlat' => array());
    array_walk_recursive($data, function(&$val, $k, &$flatObj){ $flatObj->aFlat[] = $val;}, $flatData);
    $flatData= $flatData->aFlat;

    echo PHP_EOL.buildInsertQuery($data).PHP_EOL;
    $stmt = $pdo->prepare(buildInsertQuery($data));
    $res = $stmt->execute($flatData);
    return $res;
  }

  $html = getRemote('https://www.bills.ru');
  $data = parse($html);
  saveInDb($data);

  