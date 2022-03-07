<?php
/*
*  Import module CSV->MySQL
*/
/*
*  global setup
*/
include 'config.php';

// если флаг установлен скрипт отключен
if(!$options['enable']) die('Скрипт отключен, дальнейшая обработка данных невозможна!');

// из импортируемого файла выбираем данные в массив

function csv_to_array($filename='') {
  if(!file_exists($filename) || !is_readable($filename)){
    return FALSE;
  }
  global $options;
  $header = NULL;// если файл с наименованием полей
  $data = array();
  if (($handle = fopen($filename, 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, $options['delimiter'])) !== FALSE) {
      if  ($options['header_use'] ){ // если файл с наименованием полей
        if(!$header)
          $header = $row;
        else
          $data[] = array_combine($header, $row);
      } 
      else {
        $data[] = $row;
      }
    }
    fclose($handle);
  }
  return $data;
}

// отключение индексацию таблицы, для быстродействия 
mysqli_query($link,"ALTER TABLE `".$options['db_table']."` DISABLE KEYS");

// запись в базу данных построчно
$row_f = csv_to_array($options['filename']);
$insert_row = 'INSERT INTO '.$options['db_table'].' (id, first_name, last_name, tel, firm) VALUES ';
foreach ($row_f as $val) {
  mysqli_query($link, $insert_row . '("' . implode('", "', $val) . '")');
}

// включение индексациию таблицы и закрытие соединения с БД

mysqli_query($link,"ALTER TABLE `".$options['db_base']."` ENABLE KEYS");
mysqli_close($link);

  