<?php

session_start();
require_once '../classes/Database.php';
$database = new Database();
$conn = $database->connect();

if (!isset($_GET['id'])) {
	header('Location: index.php');
	exit();
}

$linkId = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM links WHERE id = ? AND owner_id = ?");
$stmt->execute([$linkId, $_SESSION['user_id']]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$link) {
	header('Location: index.php');
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Edit Link - Link Tracker</title>
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
				<h1 class="text-2xl font-bold text-white">Edit link</h1>

				<button onclick="window.location.href='index.php'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 inline-flex items-center gap-2 font-bold transition">
					My links
				</button>
			</div>
		</div>

		<main class="flex items-center justify-center px-2 m-8" style="min-height: calc(100vh - 200px);">
			<div class="w-full max-w-md bg-neutral-800/50 border border-white/10 rounded-2xl px-12 py-10">
				<form id="editForm">
					
					<a href="index.php" class="flex items-center justify-center gap-3 text-white mb-10">
						<svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
						</svg>
						<span class="font-bold text-2xl">Edit <span class="text-orange-500">Link</span></span>
					</a>

					<div class="mb-5">
						<label for="title" class="block mb-2 text-white font-semibold text-sm">Name:</label>
						<input 
							type="text" 
							id="title" 
							name="title" 
							value="<?php echo htmlspecialchars($link['title']); ?>"
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
							value="<?php echo htmlspecialchars($link['url']); ?>"
							placeholder="Type your URL..." 
							class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition" 
							required 
						/>
					</div>

					<div class="mb-5">
						<label class="block mb-2 text-white font-semibold text-sm">Short ID:</label>
						<div class="bg-neutral-900/50 border border-white/10 rounded-lg px-4 py-2.5 w-full text-white/50 text-sm">
							<?php echo htmlspecialchars($link['short_id']); ?>
						</div>
					</div>

					<button 
						type="submit"
						class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer mb-5"
					>
						Update
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
			document.getElementById('editForm').addEventListener('submit', async function(e) {
				e.preventDefault();
				
				const title = document.getElementById('title').value;
				const url = document.getElementById('url').value;
				const linkId = <?php echo $linkId; ?>;
				
				try {
					const response = await fetch('../api/links.php?id=' + linkId, {
						method: 'PUT',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({ title, url })
					});
					
					const result = await response.json();
					
					if (response.ok) {
						alert('Link updated successfully!');
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