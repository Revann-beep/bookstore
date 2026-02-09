<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Penggunaan | Book Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- BUTTON KEMBALI -->
<div class="p-4">
    <button onclick="history.back()"
        class="flex items-center gap-2 text-gray-700 hover:text-blue-600 transition font-medium">
        ← Kembali
    </button>
</div>

<div class="max-w-5xl mx-auto p-6">
    <!-- HEADER -->
    <h1 class="text-3xl font-bold text-center mb-2">Panduan Penggunaan Book Store</h1>
    <p class="text-center text-gray-600 mb-8">
        Panduan ini membantu pengguna memahami cara menggunakan fitur pada website Book Store
    </p>

    <!-- PANDUAN -->
    <div class="space-y-4">

        <!-- LOGIN -->
        <div class="bg-white rounded shadow">
            <button onclick="toggle(1)"
                class="w-full p-4 text-left font-semibold flex justify-between">
                Cara Login
                <span>+</span>
            </button>
            <div id="content1" class="hidden px-4 pb-4 text-gray-600">
                <ol class="list-decimal ml-5 space-y-1">
                    <li>Buka halaman login Book Store.</li>
                    <li>Masukkan email dan password.</li>
                    <li>Klik tombol <b>Login</b>.</li>
                    <li>Jika berhasil, pengguna diarahkan ke dashboard.</li>
                </ol>
            </div>
        </div>

        <!-- LIHAT BUKU -->
        <div class="bg-white rounded shadow">
            <button onclick="toggle(2)"
                class="w-full p-4 text-left font-semibold flex justify-between">
                Cara Melihat Buku
                <span>+</span>
            </button>
            <div id="content2" class="hidden px-4 pb-4 text-gray-600">
                <ul class="list-disc ml-5 space-y-1">
                    <li>Pilih menu <b>Daftar Buku</b>.</li>
                    <li>Semua buku akan ditampilkan lengkap dengan harga dan stok.</li>
                    <li>Klik buku untuk melihat detail.</li>
                </ul>
            </div>
        </div>

        <!-- KERANJANG -->
        <div class="bg-white rounded shadow">
            <button onclick="toggle(3)"
                class="w-full p-4 text-left font-semibold flex justify-between">
                Cara Menambahkan Buku ke Keranjang
                <span>+</span>
            </button>
            <div id="content3" class="hidden px-4 pb-4 text-gray-600">
                <ol class="list-decimal ml-5 space-y-1">
                    <li>Pilih buku yang diinginkan.</li>
                    <li>Klik tombol <b>Tambah ke Keranjang</b>.</li>
                    <li>Buku otomatis masuk ke keranjang belanja.</li>
                </ol>
            </div>
        </div>

        <!-- CHECKOUT -->
        <div class="bg-white rounded shadow">
            <button onclick="toggle(4)"
                class="w-full p-4 text-left font-semibold flex justify-between">
                Cara Checkout
                <span>+</span>
            </button>
            <div id="content4" class="hidden px-4 pb-4 text-gray-600">
                <ol class="list-decimal ml-5 space-y-1">
                    <li>Buka halaman <b>Keranjang</b>.</li>
                    <li>Periksa jumlah dan total harga buku.</li>
                    <li>Klik tombol <b>Checkout</b>.</li>
                    <li>Pesanan akan diproses oleh sistem.</li>
                </ol>
            </div>
        </div>

        <!-- RIWAYAT -->
        <div class="bg-white rounded shadow">
            <button onclick="toggle(5)"
                class="w-full p-4 text-left font-semibold flex justify-between">
                Melihat Riwayat Pembelian
                <span>+</span>
            </button>
            <div id="content5" class="hidden px-4 pb-4 text-gray-600">
                Pengguna dapat melihat riwayat pembelian melalui menu
                <b>Pesanan Saya</b>.
            </div>
        </div>

        <!-- LOGOUT -->
        <div class="bg-white rounded shadow">
            <button onclick="toggle(6)"
                class="w-full p-4 text-left font-semibold flex justify-between">
                Cara Logout
                <span>+</span>
            </button>
            <div id="content6" class="hidden px-4 pb-4 text-gray-600">
                Klik menu <b>Logout</b> untuk keluar dari sistem Book Store.
            </div>
        </div>

    </div>
</div>

<!-- JAVASCRIPT -->
<script>
function toggle(id) {
    document.getElementById("content" + id).classList.toggle("hidden");
}
</script>

</body>
</html>