<?php

// ��������� ����������� � ���� ������
$host = 'localhost';
$db = 'j38202257_diplom';
$user = '047582029_diplom';
$pass = 'Diplom_41';

// ����������� � ���� ������
$conn = new mysqli($host, $user, $pass, $db);

// �������� �����������
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ������ �� ��������� ������
$sql = "SELECT * FROM nepoladki";
$result = $conn->query($sql);

// �������� ������� ������
if ($result->num_rows > 0) {
    // �������� ���������� ���������
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="otchet_po_oshibkam.txt"');

    // ����� ���������� �������
    echo "id\     FIO\                            otdel\          categoriy\        oshibca\              done";

    // ����� ������
    while($row = $result->fetch_assoc()) {
        echo "\n". $row['id'] . "\t" . $row['FIO'] . "\t" . $row['otdel'] . "\t" . $row['categoriy'] . "\t" . "\t" . $row['oshibca'] . "\t" . $row['done']. "\n";
    }
} else {
    echo "No results found.";
}

// �������� ����������
$conn->close();



?>