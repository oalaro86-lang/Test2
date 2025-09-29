<?php
// �������� ����������� ������
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ������������ � ���� ������
$servername = "localhost";
$username = "047582029_diplom";
$password = "Diplom_41";
$dbname = "j38202257_diplom";

$conn = new mysqli($servername, $username, $password, $dbname);

// ��������� ����������
if ($conn->connect_error) {
    die("������ �����������: " . $conn->connect_error);
}

// �������� ����������� ��� ���������� ������
if (isset($_POST['otdel'])) {
    $otdel = htmlspecialchars($_POST['otdel']);
    $sql = "SELECT name FROM users WHERE otdel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $otdel);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
        }
    } else {
        echo "<option value=''>��� ��������� �����������</option>";
    }
    $stmt->close();
}

$conn->close();
?>