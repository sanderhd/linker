<?php
session_start();
require_once 'functions/db_connection.php';
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Linker</title>
		<link rel="stylesheet" href="css/tailwind.css" />
        <link rel="shortcut icon" href="assets/link.png" type="image/x-icon">
	</head>
	<body class="bg-neutral-900">
		<nav class="mx-4 mt-6">
			<div class="max-w-5xl mx-auto bg-neutral-800/80 border border-white/10 rounded-2xl px-4 py-3 relative">
				<div class="flex items-center justify-between gap-4">
					<a href="" class="flex items-center gap-3 text-white">
						<img src="assets/link.png" alt="Logo" class="w-6 h-6" />
						<span class="font-semibold">Link <span class="text-orange-500">Tracker</span></span>
					</a>

					<a href="/links" class="absolute left-1/2 transform -translate-x-1/2 text-white/90 font-medium pointer-events-auto hover:underline hover:text-orange-500 transition">Links</a>
					
					<div class="flex items-center gap-3">
						<?php if (isset($_SESSION['user_id'])): ?>
							<a href="profile"><span class="text-white/90 text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span></a>
							<?php
								$dashboardUrl = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin/index.php' : 'dashboard/index.php';
							?>
							<a href="<?php echo $dashboardUrl; ?>" class="bg-orange-500 text-white hover:bg-orange-600 rounded px-4 py-1 text-sm font-bold transition">Dashboard</a>
							<a href="logout.php" class="text-orange-500 border border-orange-500 hover:text-white hover:bg-orange-500 rounded px-4 py-1 text-sm font-bold transition">Logout</a>
						<?php else: ?>
							<a href="login.php" class="text-orange-500 border border-orange-500 hover:text-white hover:bg-orange-500 rounded px-4 py-1 text-sm font-bold transition">Login</a>
							<a href="register.php" class="bg-orange-500 text-white border border-orange-500 hover:bg-neutral-800/80 hover:border-orange-500 hover:text-orange-500 rounded px-4 py-1 text-sm font-bold transition">Register</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</nav>

        <main class="text-white font-bold text-center mx-auto mt-20">
            <h1 class="text-5xl md:text-6xl lg:text-7xl">Best tracker for <br> your <span class="text-orange-500">links</span></h1>

            <div class="flex items-center justify-center gap-3 mt-8">
                <a href="/login" class="text-orange-500 border border-orange-500 hover:bg-white/5 rounded px-4 py-1 text-sm font-bold">Login</a>
                <a href="/register" class="bg-orange-500 text-white rounded px-4 py-1 text-sm font-bold">Register</a>
            </div>
        </main>

		<footer class="fixed bottom-0 left-0 right-0 bg-neutral-900">
			<div class="max-w-5xl mx-auto mb-6 text-center text-white/50 text-sm">
				<div class="h-px bg-white/10 mb-4"></div>

				&copy; 2026 Link Tracker. All rights reserved.
			</div>
		</footer>
	</body>
</html>
