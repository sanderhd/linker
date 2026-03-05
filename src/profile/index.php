<?php
session_start();
require_once '../functions/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Linker</title>
		<link rel="stylesheet" href="../css/tailwind.css" />
        <link rel="shortcut icon" href="../assets/link.png" type="image/x-icon">
	</head>
	<body class="bg-neutral-900">
		<nav class="mx-4 mt-6">
			<div class="max-w-5xl mx-auto bg-neutral-800/80 border border-white/10 rounded-2xl px-4 py-3 relative">
				<div class="flex items-center justify-between gap-4">
					<a href="../index.php" class="flex items-center gap-3 text-white">
						<img src="../assets/link.png" alt="Logo" class="w-6 h-6" />
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

        <main class="max-w-5xl mx-auto mt-10">
			<div class="mb-8 flex items-center justify-between">
				<h1 class="text-2xl font-bold text-white">My Profile</h1>

				<button onclick="window.location.href='../dashboard'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 inline-flex items-center gap-2 font-bold transition">
					My links
				</button>
			</div>

            <form action="index.php" method="POST">
				<div class="mb-5">
					<label for="title" class="block mb-2 text-white font-semibold text-sm">Name:</label>
					<input 
						type="text" 
						id="title" 
						name="title" 
						placeholder="Type your link name..." 
						class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition" 
						required 
					/>
				</div>
					
				<div class="mb-2">
					<label for="url" class="block mb-2 text-white font-semibold text-sm">URL:</label>
					<input 
						type="url" 
						id="url" 
						name="url" 
						placeholder="Type your URL..." 
						class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition" 
						required 
					/>
				</div>

				<button 
					type="submit" 
					name="profile"
					class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer mb-5"
				>
					Create
				</button>
			</form>
        </main>

		<footer class="fixed bottom-0 left-0 right-0 bg-neutral-900">
			<div class="max-w-5xl mx-auto mb-6 text-center text-white/50 text-sm">
				<div class="h-px bg-white/10 mb-4"></div>

				&copy; 2026 Link Tracker. All rights reserved.
			</div>
		</footer>
	</body>
</html>
