<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SARI ANGREK - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .active-nav {
            background-color: #e6fffa;
            color: #0d9488;
            border-left: 4px solid #0d9488;
        }
        .status-dikirim {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-ditolak {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .hover-row:hover {
            background-color: #f9fafb;
        }
        .delete-btn {
            transition: all 0.2s;
        }
        .delete-btn:hover {
            transform: scale(1.05);
        }
        .modal {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed left-0 top-0 h-screen w-64 bg-white shadow-lg">
        <!-- Header -->
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-teal-700">SARI ANGREK</h1>
            <p class="text-sm text-gray-500 mt-1">Admin Dashboard</p>
        </div>
        
        <!-- Navigation -->
        <nav class="p-4 space-y-1">
            <!-- Dashboard -->
            <div class="mb-4">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">Dashboard</h3>
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="produk.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-box w-5"></i>
                    <span>Produk</span>
                </a>
            </div>
            
            <!-- Approve -->
            <div class="mb-4">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">Approve</h3>
                <a href="laporan.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg active-nav">
                    <i class="fas fa-check-circle w-5"></i>
                    <span>Laporan</span>
                </a>
                <a href="chat.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-comments w-5"></i>
                    <span>Chat</span>
                </a>
            </div>
            
            <!-- Account -->
            <div class="mb-4">
                <a href="admin.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-user-circle w-5"></i>
                    <span>My Account</span>
                </a>
                <a href="../auth/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Sign Out</span>
                </a>
            </div>
            
            <!-- Help -->
            <div class="mt-8 pt-6 border-t">
                <a href="help.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-500 hover:text-teal-600 hover:bg-teal-50">
                    <i class="fas fa-question-circle w-5"></i>
                    <span>Help</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-6">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Approve</h2>
            <p class="text-gray-600">Kelola persetujuan pesanan pelanggan</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Tambah Pesanan
                </button>
                <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-download"></i>
                    Export CSV
                </button>
            </div>
            <div class="text-sm text-gray-500">
                Total: 3 Pesanan
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QTY</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti TF</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Resi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat Pembeli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pembeli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Row 1 - Disetujui -->
                        <tr class="hover-row">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">A01</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">IPAS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="text-teal-600 hover:text-teal-800" onclick="viewProof('A01')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">Transfer</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded status-dikirim">Dikirim</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">JNE123</td>
                            <td class="px-6 py-4 text-sm text-gray-900">JL JAKARTA RAYA</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Diana</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1.5 bg-teal-100 text-teal-700 rounded text-xs hover:bg-teal-200 flex items-center gap-1" onclick="inputResi('A01')">
                                        <i class="fas fa-truck"></i> Resi
                                    </button>
                                    <button class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200 flex items-center gap-1" onclick="showDetail('A01')">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Row 2 - Ditolak -->
                        <tr class="hover-row">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">A02</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">PHP</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="text-teal-600 hover:text-teal-800" onclick="viewProof('A02')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">Transfer</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded status-ditolak">Ditolak</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 text-sm text-gray-900">JL BEKASI RAYA</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Adinda</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1.5 bg-teal-100 text-teal-700 rounded text-xs hover:bg-teal-200 flex items-center gap-1" onclick="inputResi('A02')">
                                        <i class="fas fa-truck"></i> Resi
                                    </button>
                                    <button class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200 flex items-center gap-1" onclick="showDetail('A02')">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                    <button class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200 flex items-center gap-1 delete-btn" onclick="confirmDelete('A02', 'PHP', 'Adinda')">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Row 3 - Pending Approval -->
                        <tr class="hover-row">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">A03</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Java</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="text-teal-600 hover:text-teal-800" onclick="viewProof('A03')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">Transfer</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">Pending</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 text-sm text-gray-900">JL DEPOK RAYA</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Bagas</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1.5 bg-teal-100 text-teal-700 rounded text-xs hover:bg-teal-200 flex items-center gap-1" onclick="inputResi('A03')">
                                        <i class="fas fa-truck"></i> Resi
                                    </button>
                                    <button class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200 flex items-center gap-1" onclick="showDetail('A03')">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Menampilkan 3 dari 3 pesanan
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                            Previous
                        </button>
                        <button class="px-3 py-1 bg-teal-600 text-white rounded text-sm hover:bg-teal-700">
                            1
                        </button>
                        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Note -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>@nurhayatulfadilla | Dashboard Admin SARI ANGREK</p>
        </div>
    </div>

    <!-- MODAL INPUT RESI -->
    <div id="resiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full modal">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Input Nomor Resi</h3>
                    <button onclick="closeResiModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Kode Pesanan:</span> <span id="resiKode">-</span>
                    </p>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Judul Buku:</span> <span id="resiBuku">-</span>
                    </p>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Nama Pembeli:</span> <span id="resiPembeli">-</span>
                    </p>
                </div>
                
                <form id="resiForm" onsubmit="submitResi(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No Resi</label>
                            <input type="text" id="noResi" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Paket Kurir</label>
                            <select id="paketKurir" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                                <option value="">Pilih kurir</option>
                                <option value="JNE">JNE</option>
                                <option value="J&T">J&T</option>
                                <option value="POS Indonesia">POS Indonesia</option>
                                <option value="TIKI">TIKI</option>
                                <option value="SiCepat">SiCepat</option>
                            </select>
                        </div>
                        
                        <div class="pt-4 border-t">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Link Lacak Paket</label>
                            <input type="url" id="linkLacak" placeholder="https://..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeResiModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">
                            <i class="fas fa-save mr-2"></i> Simpan Resi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL PRODUK -->
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-lg max-w-lg w-full modal">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Pesanan</h3>
                    <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Detail Content -->
                <div id="detailContent" class="space-y-4 mb-6">
                    <!-- Content akan diisi oleh JavaScript -->
                </div>
                
                <!-- Bukti TF -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Bukti Transfer</h4>
                    <div id="buktiTF" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        <!-- Bukti TF akan ditampilkan di sini -->
                    </div>
                </div>
                
                <!-- Approve/Ditolak Options -->
                <div class="border-t pt-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-4">Aksi Persetujuan</h4>
                    <div class="flex space-x-3">
                        <button id="btnApprove" onclick="approveOrder()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Setujui Pesanan
                        </button>
                        <button id="btnReject" onclick="rejectOrder()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i> Tolak Pesanan
                        </button>
                    </div>
                    <div id="resiSection" class="mt-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">No Resi (jika disetujui)</label>
                        <input type="text" id="resiInput" placeholder="Masukkan no resi"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full modal">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus data ini?</p>
                    </div>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-sm font-medium text-red-800 mb-1">Data yang akan dihapus:</p>
                    <div class="text-sm text-gray-700">
                        <p><span class="font-medium">Kode Pesanan:</span> <span id="deleteKode">-</span></p>
                        <p><span class="font-medium">Judul Buku:</span> <span id="deleteBuku">-</span></p>
                        <p><span class="font-medium">Nama Pembeli:</span> <span id="deletePembeli">-</span></p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                        Batal
                    </button>
                    <button onclick="deleteOrder()" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                        <i class="fas fa-trash-alt mr-2"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentOrder = null;
        let currentOrderToDelete = null;
        
        // DATA SAMPLE
        const orderData = {
            'A01': {
                kode: 'A01',
                buku: 'IPAS',
                qty: 2,
                pembeli: 'Diana',
                alamat: 'JL JAKARTA RAYA',
                status: 'Dikirim',
                resi: 'JNE123',
                harga: 'Rp 150.000',
                tanggal: '15 Jan 2024',
                buktiTF: 'bukti_tf_01.jpg'
            },
            'A02': {
                kode: 'A02',
                buku: 'PHP',
                qty: 3,
                pembeli: 'Adinda',
                alamat: 'JL BEKASI RAYA',
                status: 'Ditolak',
                resi: '-',
                harga: 'Rp 225.000',
                tanggal: '14 Jan 2024',
                buktiTF: 'bukti_tf_02.jpg'
            },
            'A03': {
                kode: 'A03',
                buku: 'Java',
                qty: 2,
                pembeli: 'Bagas',
                alamat: 'JL DEPOK RAYA',
                status: 'Pending',
                resi: '-',
                harga: 'Rp 200.000',
                tanggal: '13 Jan 2024',
                buktiTF: 'bukti_tf_03.jpg'
            }
        };

        // ========== FUNGSI INPUT RESI ==========
        function inputResi(kode) {
            currentOrder = kode;
            const order = orderData[kode];
            
            document.getElementById('resiKode').textContent = kode;
            document.getElementById('resiBuku').textContent = order.buku;
            document.getElementById('resiPembeli').textContent = order.pembeli;
            
            // Reset form
            document.getElementById('resiForm').reset();
            
            // Tampilkan modal
            document.getElementById('resiModal').classList.remove('hidden');
            document.getElementById('resiModal').classList.add('flex');
        }

        function closeResiModal() {
            document.getElementById('resiModal').classList.add('hidden');
            document.getElementById('resiModal').classList.remove('flex');
            currentOrder = null;
        }

        function submitResi(event) {
            event.preventDefault();
            
            const noResi = document.getElementById('noResi').value;
            const paketKurir = document.getElementById('paketKurir').value;
            const linkLacak = document.getElementById('linkLacak').value;
            
            // Simpan ke database (simulasi)
            alert(`Resi berhasil disimpan!\nNo Resi: ${noResi}\nKurir: ${paketKurir}\nLink Lacak: ${linkLacak}`);
            
            // Update status di tabel
            if (currentOrder) {
                // Di aplikasi nyata, ini akan update database
                showNotification(`Resi untuk pesanan ${currentOrder} berhasil disimpan!`, 'success');
            }
            
            closeResiModal();
        }

        // ========== FUNGSI DETAIL PRODUK ==========
        function showDetail(kode) {
            currentOrder = kode;
            const order = orderData[kode];
            
            // Isi detail content
            const detailContent = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Kode Pesanan</p>
                        <p class="font-medium text-gray-800">${order.kode}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Pesan</p>
                        <p class="font-medium text-gray-800">${order.tanggal}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Judul Buku</p>
                        <p class="font-medium text-gray-800">${order.buku}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jumlah</p>
                        <p class="font-medium text-gray-800">${order.qty} buku</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Harga</p>
                        <p class="font-medium text-gray-800">${order.harga}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama Pembeli</p>
                        <p class="font-medium text-gray-800">${order.pembeli}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Alamat Pengiriman</p>
                        <p class="font-medium text-gray-800">${order.alamat}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('detailContent').innerHTML = detailContent;
            
            // Isi bukti TF
            const buktiTF = `
                <div class="flex flex-col items-center">
                    <div class="mb-2">
                        <i class="fas fa-file-invoice text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${order.buktiTF}</p>
                    <button onclick="viewProof('${order.kode}')" class="px-3 py-1.5 bg-teal-600 text-white rounded text-sm hover:bg-teal-700">
                        <i class="fas fa-eye mr-1"></i> Lihat Bukti Transfer
                    </button>
                </div>
            `;
            
            document.getElementById('buktiTF').innerHTML = buktiTF;
            
            // Tampilkan modal
            document.getElementById('detailModal').classList.remove('hidden');
            document.getElementById('detailModal').classList.add('flex');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
            document.getElementById('detailModal').classList.remove('flex');
            currentOrder = null;
        }

        // ========== FUNGSI APPROVE/TOLAK ==========
        function approveOrder() {
            if (!currentOrder) return;
            
            const resiInput = document.getElementById('resiInput').value;
            if (!resiInput.trim()) {
                // Tampilkan input resi
                document.getElementById('resiSection').classList.remove('hidden');
                document.getElementById('resiInput').focus();
                return;
            }
            
            // Simpan approval ke database (simulasi)
            alert(`Pesanan ${currentOrder} DISETUJUI!\nNo Resi: ${resiInput}`);
            showNotification(`Pesanan ${currentOrder} berhasil disetujui!`, 'success');
            
            // Update status
            orderData[currentOrder].status = 'Dikirim';
            orderData[currentOrder].resi = resiInput;
            
            closeDetailModal();
            // Di aplikasi nyata, reload data atau update tabel
        }

        function rejectOrder() {
            if (!currentOrder) return;
            
            if (confirm(`Apakah Anda yakin ingin MENOLAK pesanan ${currentOrder}?`)) {
                // Simpan rejection ke database (simulasi)
                alert(`Pesanan ${currentOrder} DITOLAK!`);
                showNotification(`Pesanan ${currentOrder} telah ditolak.`, 'warning');
                
                // Update status
                orderData[currentOrder].status = 'Ditolak';
                orderData[currentOrder].resi = '-';
                
                // Tampilkan tombol hapus
                showDeleteButton(currentOrder);
                
                closeDetailModal();
                // Di aplikasi nyata, reload data atau update tabel
            }
        }

        // ========== FUNGSI HAPUS ==========
        function confirmDelete(kode, buku, pembeli) {
            currentOrderToDelete = kode;
            document.getElementById('deleteKode').textContent = kode;
            document.getElementById('deleteBuku').textContent = buku;
            document.getElementById('deletePembeli').textContent = pembeli;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
            currentOrderToDelete = null;
        }

        function deleteOrder() {
            if (currentOrderToDelete) {
                // Hapus dari database (simulasi)
                alert(`Pesanan ${currentOrderToDelete} berhasil dihapus!`);
                showNotification(`Pesanan ${currentOrderToDelete} berhasil dihapus!`, 'success');
                
                // Di aplikasi nyata, hapus dari database dan refresh tabel
                closeDeleteModal();
            }
        }

        // ========== FUNGSI BANTUAN ==========
        function viewProof(kode) {
            const order = orderData[kode];
            alert(`Membuka bukti transfer: ${order.buktiTF}\n\nKode: ${kode}\nPembeli: ${order.pembeli}`);
        }

        function showDeleteButton(kode) {
            // Di aplikasi nyata, ini akan update tampilan tabel
            console.log(`Tombol hapus untuk pesanan ${kode} ditampilkan`);
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white z-50 transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-600' : type === 'warning' ? 'bg-yellow-600' : 'bg-red-600'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Event listener untuk ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeResiModal();
                closeDetailModal();
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>