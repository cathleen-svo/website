<?php
include 'koneksi.php';

// Ambil data kantin dan menu
$kantins = $conn->query("SELECT * FROM kantin");
?>

<!doctype html> 
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kantin Sekolah SMK Telkom Jakarta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      padding-top: 70px; 
      background-color: maroon;
      color :white
    }
    section {
      padding: 60px 0;
      border-bottom: 1px solid #ddd;
      scroll-margin-top: 70px; 
    }
    .menu-img {
      max-width: 100%;
      height: auto;
    }
  </style>
</head>
<body>


<nav style="background-color: #007bff; padding: 10px 20px; position: fixed; top: 0; width: 100%; z-index: 1000;">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div style="color: white; font-weight: bold; font-size: 18px;">
      Kantin TELKOM
    </div>
    <div>
      <a href="#about" style="color: white; margin: 0 10px; text-decoration: none;">About Canteen</a>
      <a href="#cafeteria" style="color: white; margin: 0 10px; text-decoration: none;">Cafeteria List</a>
      <a href="#howto" style="color: white; margin: 0 10px; text-decoration: none;">How to Buy</a>
      <a href="#contact" style="color: white; margin: 0 10px; text-decoration: none;">Contact Us</a>
    </div>
  </div>
</nav>


<main class="container">

  
  <section id="about" class="text-center">
    <h1 class="mb-4">About Canteen</h1>
    <img src="logo_kantin.png" alt="Logo Kantin" class="img-fluid mb-3 mx-auto" style="max-height: 120px;" />
    <div class="row justify-content-center align-items-center">
      <div class="col-md-6">
        <img src="kantin_sekolah.jpg" alt="Foto Kantin" class="img-fluid rounded mb-3" />
        <video controls class="w-100 rounded" style="max-height: 300px;">
          <source src="kantin_video.mp4" type="video/mp4" />
          Browsermu tidak mendukung video.
        </video>
      </div>
      <div class="col-md-6 text-start">
        <p>Kantin SMK Telkom menyediakan berbagai makanan dan minuman berkualitas dengan harga terjangkau. Kami berkomitmen memberikan pelayanan terbaik untuk pelanggan kami.</p>
      </div>
    </div>
  </section>

  
  <section id="cafeteria">
    <h2 class="mb-4 text-center">Cafeteria List</h2>
    <?php 
    if ($kantins->num_rows > 0):
      while ($kantin = $kantins->fetch_assoc()):
    ?>
      <h3 class="mt-4"><?= htmlspecialchars($kantin['nama_kantin']) ?></h3>
      <div class="row">
        <?php
        $menus_for_kantin = $conn->query("SELECT * FROM menu WHERE id_kantin = " . (int)$kantin['id'] . " LIMIT 4");
        if ($menus_for_kantin->num_rows > 0):
          while ($menu = $menus_for_kantin->fetch_assoc()):
        ?>
          <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
              <img src="<?= htmlspecialchars($menu['foto']) ?>" class="card-img-top menu-img" alt="<?= htmlspecialchars($menu['nama']) ?>" />
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($menu['nama']) ?></h5>
                <p class="card-text">Rp <?= number_format($menu['harga']) ?></p>
                <p class="card-text">Stok: <?= (int)$menu['stok'] ?></p>
              </div>
            </div>
          </div>
        <?php
          endwhile;
        else:
        ?>
          <p class="text-muted">Tidak ada menu tersedia.</p>
        <?php endif; ?>
      </div>
    <?php
      endwhile;
    else:
    ?>
      <p class="text-muted">Tidak ada kantin tersedia.</p>
    <?php endif; ?>
  </section>


  <section id="howto">
    <h2 class="mb-4 text-center">How to Buy</h2>
    <form id="orderForm" class="mb-4" onsubmit="return false;">
      <div class="mb-3">
        <label for="namaPembeli" class="form-label">Nama Pembeli</label>
        <input type="text" id="namaPembeli" name="nama" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">Pilih Menu</label>
        <select id="menuSelect" class="form-select" required>
          <option value="" disabled selected>-- Pilih Menu --</option>
          <?php
          $allMenus = $conn->query("SELECT m.id, m.nama, m.harga, m.stok, k.nama_kantin FROM menu m JOIN kantin k ON m.id_kantin = k.id WHERE m.stok > 0");
          while ($m = $allMenus->fetch_assoc()):
          ?>
            <option value="<?= $m['id'] ?>" data-harga="<?= $m['harga'] ?>" data-stok="<?= $m['stok'] ?>">
              <?= htmlspecialchars($m['nama']) ?> (<?= htmlspecialchars($m['nama_kantin']) ?>) - Rp<?= number_format($m['harga']) ?> - Stok: <?= $m['stok'] ?>
            </option> 
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="jumlahPesan" class="form-label">Jumlah</label>
        <input type="number" id="jumlahPesan" class="form-control" min="1" value="1" required />
      </div>
      <button type="button" class="btn btn-success" onclick="tambahPesanan()">Tambah Pesanan</button>
    </form>

    <h3>Ringkasan Pesanan</h3>
    <ul id="listPesanan" class="list-group mb-3"></ul>
    <p><strong>Total Harga: Rp <span id="totalHarga">0</span></strong></p>

    <div id="qrCodeDummy" class="mb-3" style="display:none;">
      <h5>QR Code Pemesanan</h5>
      <img src="dummy_qr.png" alt="QR Code" class="img-fluid" style="max-width:200px;" />
    </div>

    <button type="button" class="btn btn-primary" onclick="submitPesanan()">Submit Pesanan</button>
  </section>


  <section id="contact">
    <h2 class="mb-4 text-center">Contact Us</h2>
    <form id="contactForm" method="POST" action="kirimPesan.php">
      <div class="mb-3">
        <label for="contactNama" class="form-label">Nama</label>
        <input type="text" id="contactNama" name="nama" class="form-control" required />
      </div>
      <div class="mb-3">
        <label for="contactEmail" class="form-label">Email</label>
        <input type="email" id="contactEmail" name="email" class="form-control" required />
      </div>
      <div class="mb-3">
        <label for="contactPesan" class="form-label">Pesan</label>
        <textarea id="contactPesan" name="pesan" class="form-control" rows="4" required></textarea>
      </div>
      <button type="submit" class="btn btn-secondary">Kirim</button>
    </form>
  </section>

  <footer class="text-center mt-5 mb-3">
    <hr />
    <p>&copy; 2025 foodGo - All rights reserved</p>
  </footer>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  let pesanan = [];
  let totalHarga = 0;

  function tambahPesanan() {
    const select = document.getElementById('menuSelect');
    const jumlahInput = document.getElementById('jumlahPesan');
    const namaPembeli = document.getElementById('namaPembeli').value.trim();

    const menuId = select.value;
    const jumlah = parseInt(jumlahInput.value);
    const harga = parseInt(select.options[select.selectedIndex].dataset.harga);
    const stok = parseInt(select.options[select.selectedIndex].dataset.stok);
    const namaMenu = select.options[select.selectedIndex].text;

    if (!menuId) {
      alert('Pilih menu terlebih dahulu.');
      return;
    }

    if (jumlah < 1 || isNaN(jumlah)) {
      alert('Jumlah harus minimal 1.');
      return;
    }

    if (jumlah > stok) {
      alert('Jumlah melebihi stok yang tersedia.');
      return;
    }

    if (namaPembeli === '') {
      alert('Isi nama pembeli terlebih dahulu.');
      return;
    }

    // Cek apakah menu sudah ada di pesanan, jika ada tambah jumlah
    let found = false;
    for (let item of pesanan) {
      if (item.id === menuId) {
        if (item.jumlah + jumlah > stok) {
          alert('Total jumlah pesanan melebihi stok yang tersedia.');
          return;
        }
        item.jumlah += jumlah;
        found = true;
        break;
      }
    }

    if (!found) {
      pesanan.push({ id: menuId, nama: namaMenu, jumlah: jumlah, harga: harga });
    }

    updatePesanan();

    // Reset input jumlah ke 1
    jumlahInput.value = 1;
  }

  
 function updatePesanan() {
  const list = document.getElementById('listPesanan');
  list.innerHTML = '';
  totalHarga = 0;

  pesanan.forEach((item, idx) => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.textContent = `${item.nama} x${item.jumlah}`;
    const span = document.createElement('span');
    span.textContent = 'Rp ' + (item.harga * item.jumlah).toLocaleString();
    li.appendChild(span);
    list.appendChild(li);

    totalHarga += item.harga * item.jumlah;
  });

  document.getElementById('totalHarga').textContent = totalHarga.toLocaleString();
}


  function submitPesanan() {
  if (pesanan.length === 0) {
    alert('Tambahkan pesanan terlebih dahulu');
    return;
  }

  const namaPembeli = document.getElementById('namaPembeli').value;

  fetch('simpanPesanan.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ nama: namaPembeli, pesanan: pesanan })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      alert('Pesanan berhasil dikirim!');

      // tampilkan QR code dummy
      document.getElementById('qrCodeDummy').style.display = 'block';

      // baru reset pesanan
      pesanan = [];
      updatePesanan();
    } else {
      alert('Gagal menyimpan pesanan!');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Terjadi kesalahan saat mengirim pesanan');
  });
}



</script>

</body>
</html>
