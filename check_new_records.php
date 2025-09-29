<?php
session_start();

$myspl = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

if ($myspl->connect_error) {
    die("������ �����������: " . $myspl->connect_error);
}

// �������� ������� �����
$currentTime = date('Y-m-d H:i:s');

// ��������� ����� 30 ������ �����
$timeLimit = date('Y-m-d H:i:s', strtotime($currentTime) - 30);

// �������� ����� ������ �� ��������� 30 ������
$query = "SELECT * FROM `nepoladki` WHERE `DateTime` > '$timeLimit'";
$result = $myspl->query($query);

$newRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $newRecords[] = $row;
    }
}

// ���������� ����� ������ � ������� JSON
echo json_encode($newRecords);

$myspl->close();
?>