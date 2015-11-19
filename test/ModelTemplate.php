<?php

require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . 'smoothTransport/model/ModelTemplate.php';
require_once filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . 'smoothTransport/model/DBConnection.php';

$truck = new ModelTemplate('Truck');
/*
 * SUBMIT NEW TEST.
 */
$truck->vehicle_capacity = 3;
$truck->brand = 'Scania';
$truck->age = 3;
$lastInsertedId = $truck->submit_new();

echo '<p> Last Inserted ID: ' . $lastInsertedId . '</p>';
/*
 * GET SINGLE TEST.
 */
$truck2 = new ModelTemplate('Truck');
$truck2 = $truck2->get_single($lastInsertedId);
echo '<p> One element: ';
var_dump($truck2);
echo '</p>';

/*
 * GET ALL TEST.
 */
$truck3 = new ModelTemplate('Truck');
$trucks = $truck3->get_all();
echo '<p> All elements: ';
var_dump($trucks);

/*
 * SUBMIT CHANGES TEST.
 */
$truck4 = new ModelTemplate('Truck');
echo '<p> Before update: ';
var_dump($truck4 = $truck4->get_single($lastInsertedId));
echo '</p>';

$truck4->vehicle_capacity = 100;
$truck4->brand = 'MAN';
$truck4->age = 10;
$truck4->submit_changes();

echo '<p> After update: ';
var_dump($truck4->get_single($lastInsertedId));
echo '</p>';

/*
 * DELETE TEST.
 */
$truck5 = new ModelTemplate('Truck');
$truck5 = $truck5->get_single($lastInsertedId);
echo '<p> Deleted rows: ' . $truck5->delete() . '</p>';