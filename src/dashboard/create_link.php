<?php

session_start();
require_once '../classes/Database.php';
$database = new Database();
$conn = $database->connect();

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Create Link - Link Tracker</title>
		<link rel="stylesheet" href="../css/tailwind.css" />
		<link rel="shortcut icon" href="../assets/link.png" type="image/x-icon">
	</head>
	<body class="bg-neutral-900 min-h-screen">
		<nav class="mx-4 mt-6">
			<div class="max-w-5xl mx-auto bg-neutral-800/80 border border-white/10 rounded-2xl px-4 py-3 relative">
				<div class="flex items-center justify-between gap-4">
					<a href="" class="flex items-center gap-3 text-white">
						<img src="../assets/link.png" alt="Logo" class="w-6 h-6" />
						<span class="font-semibold">Link <span class="text-orange-500">Tracker</span></span>
					</a>

					<a href="/links" class="absolute left-1/2 transform -translate-x-1/2 text-white/90 font-medium pointer-events-auto hover:underline hover:text-orange-500 transition">Links</a>
					
					<div class="flex items-center gap-3">
						<?php if (isset($_SESSION['user_id'])): ?>
							<a href="profile"><span class="text-white/90 text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span></a>
							<?php
								$dashboardUrl = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? '../admin/index.php' : '../dashboard/index.php';
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

		<div class="max-w-5xl mx-auto mt-10">
			<div class="mb-8 flex items-center justify-between">
				<h1 class="text-2xl font-bold text-white">Create link</h1>

				<button onclick="window.location.href='index.php'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 inline-flex items-center gap-2 font-bold transition">
					My links
				</button>
			</div>
		</div>

		<main class="flex items-center justify-center px-2 m-8" style="min-height: calc(100vh - 200px);">
			<div class="w-full max-w-md bg-neutral-800/50 border border-white/10 rounded-2xl px-12 py-10">
				<form action="create_link.php" method="POST">
					
					<a href="index.php" class="flex items-center justify-center gap-3 text-white mb-10">
						<svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
						</svg>
						<span class="font-bold text-2xl">New <span class="text-orange-500">Link</span></span>
					</a>

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
						name="create_link"
						class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer mb-5"
					>
						Create
					</button>
				</form>
			</div>
		</main>

		<footer class="bg-neutral-900 mt-auto">
			<div class="max-w-5xl mx-auto mb-6 text-center text-white/50 text-sm">
				<div class="h-px bg-white/10 mb-4"></div>

				&copy; 2026 Link Tracker. All rights reserved.
			</div>
		</footer>

		<script>
			document.querySelector('form').addEventListener('submit', async function(e) {
				e.preventDefault();
				
				const title = document.getElementById('title').value;
				const url = document.getElementById('url').value;
				
				try {
					const response = await fetch('../api/links.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({ title, url })
					});
					
					const result = await response.json();
					
					if (response.ok) {
						alert('Link created successfully! Short URL: ' + window.location.origin + '/l/' + result.short_id);
						window.location.href = 'index.php';
					} else {
						alert('Error: ' + result.message);
					}
				} catch (error) {
					alert('An error occurred: ' + error.message);
				}
			});
		</script>
	</body>
</html>