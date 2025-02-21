<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../public/style.css"> 
</head>
<body>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 px-4">
        <!-- <img src="../img/Screenshot__248_-removebg-preview.png" alt="luxury_vibes" class="w-64"> -->
    <section class="bg-white shadow-lg rounded-lg w-full max-w-3xl grid grid-cols-1 md:grid-cols-2 overflow-hidden h-[500px]">
        <!-- Bagian Kiri (Form Login) -->
        <div class="p-8 flex flex-col justify-center form-container">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Masuk</h2>
                <h3 class="text-lg"><a href="register.php" class="text-blue-600 hover:underline">Daftar</a></h3>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($_SESSION['error'])): ?>
                <p class="text-red-500 text-sm text-center mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <!-- Form -->
            <form action="../auth/login.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700">Username:</label>
                    <input type="text" name="username" required class="w-full px-4 py-2 border rounded-lg focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700">Password:</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none">
                </div>
                <button type="submit" name="login" class="btn-login w-full text-white py-2 rounded-lg hover:bg-blue-700 transition">Login</button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-4">Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Daftar</a></p>
        </div>

        <!-- Bagian Kanan (Welcome Text) -->
        <div class="side-card text-white flex flex-col justify-center items-center p-8 content-container">
            <h3 class="text-2xl font-semibold mb-2">Selamat Datang Kembali!</h3>
            <p class="text-center text-sm">masukkan detail pribadi Anda dan mulailah perjalanan bersama kami.</p>
        </div>
    </section>
    </div>
    <footer class="text-center mt-auto">
        <p>2025 by @jose</p>
    </footer>
</body>
</html>

<style>

* {
    box-sizing: border-box;
    overflow: hidden;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

footer{
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(-100%);
        opacity: 1;
    }
    to {
        transform: translateX(0);
        opacity: 0;
    }
}


.page-load .form-container {
    animation: slideIn 0.5s ease-in-out forwards;
}

.page-load .content-container {
    animation: slideOut 0.5s ease-in-out forwards;
}

.side-card {
    background:#07A568;
}

.btn-login {
    background:#07A568;
}
</style>

<script>
    document.body.classList.add('page-load');
    setTimeout(() => {
        document.body.classList.remove('page-load');
    }, 500); 
</script>
