<?
  use Zend\Db\Adapter\Driver\ResultInterface;
  use Zend\Db\ResultSet\ResultSet;

  require_once './vendor/autoload.php';
  require_once './init.php';

  $initObj = new init();
  $result = $initObj->get();
  
  if($result instanceof ResultInterface && $result->isQueryResult()) {
    $resultSet = new ResultSet;
    $resultSet->initialize($result);
    print_r($resultSet);
    echo "<table>";
    echo "<tr>
            <td>id</td>
            <td>sript name</td>
            <td>start time</td>
            <td>end time</td>
            <td>result</td>
          </tr>";
          
    foreach ($resultSet as $row) {
      echo "
      <tr>
        <td>
          $row->id
        </td>
        <td>
          $row->script_name
        </td>
        <td>
          $row->start_time
        </td>
        <td>
          $row->end_time
        </td>
        <td>
          $row->result
        </td>
      <tr>";
    }
    echo "</table>";
  }
?>