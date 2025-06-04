<?php
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

$nama = $data['nama'];
$items = $data['pesanan'];

foreach ($items as $item) {
    $id_menu = $item['id'];
    $jumlah = $item['jumlah'];

    
    $result = $conn->query("SELECT harga, stok FROM menu WHERE id = $id_menu");
    $row = $result->fetch_assoc();

    $harga = $row['harga'];
    $stok = $row['stok'];

    $total = $harga * $jumlah;

    if ($jumlah > $stok) continue; 

   
    $stmt = $conn->prepare("INSERT INTO pesanan (nama_pelanggan, id_menu, jumlah, total_harga) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $nama, $id_menu, $jumlah, $total);
    $stmt->execute();

   
    $conn->query("UPDATE menu SET stok = stok - $jumlah WHERE id = $id_menu");
}

echo json_encode(['status' => 'success']);
?>