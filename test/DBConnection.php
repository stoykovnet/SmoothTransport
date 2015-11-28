<?php

require_once '../model/DBConnection.php';

$db = new DBConnection();

/*
 * INSERT TEST.
 */
$lastInsertedId = $db->insert('truck', array(
    'vehicle_capacity' => 0,
    'brand' => 'Scania',
    'age' => 4
        ));
echo '<p> Last Inserted ID: ' . $lastInsertedId . '</p>';

/*
 * SELECT SINGLE TEST.
 */
echo '<p> All columns: ';
var_dump($db->select('truck', '*', 'id', $lastInsertedId));
echo '</p>';

echo '<p> Some columns: ';
var_dump($db->select('truck', array('brand', 'age'), 'id', $lastInsertedId));
echo '</p>';

/*
 * SELECT ALL TEST.
 */
$db->insert('truck', array(
    'brand' => 'Mercedes',
    'age' => 3
));
echo '<p> All rows and columns: ';
var_dump($db->select('truck', '*'));

/*
 * UPDATE TEST.
 */
echo '<p> Before update: ';
var_dump($db->select('truck', '*', 'id', $lastInsertedId));
echo '</p>';

$db->update('truck', array(
    'vehicle_capacity' => 0,
    'brand' => 'MAN',
    'age' => 0
), $lastInsertedId);

echo '<p> After update: ';
var_dump($db->select('truck', '*', 'id',$lastInsertedId));
echo '</p>';

/*
 * DELETE TEST.
 */
echo '<p> Deleted rows: ' . $db->delete('truck', $lastInsertedId) . '</p>';
echo '<p> Deleted rows: ' . $db->delete('truck', $lastInsertedId + 1) . '</p>';

/*
 * GET TABLE COLUMNS NAMES TEST.
 */
var_dump($db->get_table_columns_names('truck'));