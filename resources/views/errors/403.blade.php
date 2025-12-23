<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center">
    <div class="w-full max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-blue-600 p-8 text-white text-center">
                <div class="text-7xl mb-6">
                    <i class="fas fa-ban"></i>
                </div>
                <h1 class="text-4xl font-bold mb-2">403</h1>
                <h2 class="text-2xl font-semibold">Akses Ditolak</h2>
                <p class="mt-4 text-blue-100">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            </div>
            <div class="p-8 text-center">
                <p class="text-gray-600 mb-6">
                    Sepertinya Anda mencoba mengakses sumber daya yang tidak diizinkan untuk peran Anda.<br>
                    Silakan hubungi administrator sistem jika Anda yakin ini adalah kesalahan.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="/admin" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300 flex items-center">
                        <i class="fas fa-home mr-2"></i>
                        Kembali ke Dashboard
                    </a>
                    <a href="/admin/logout" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-300 flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>