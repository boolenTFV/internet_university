<?
  /**
   *  Скрипт, который в папке /datafiles находит все файлы, имена 
   *  которых состоят из цифр и букв латинского алфавита, имеют 
   *  расширение ixt и выводит на экран имена этих файлов, 
   *  упорядоченных по имени.
   */

  define ( 'DATAFILES' , './datafiles'); 
  
  $content = scandir(DATAFILES);

  $result = preg_grep("/^[0-9a-zA-Z]+\.ixt$/", $content);
  foreach( $result as $file ){
    if( !is_dir(DATAFILES . DIRECTORY_SEPARATOR . $file)){
      echo $file.PHP_EOL;
    }
  }