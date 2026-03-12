<?php
session_start();
require_once '../classes/Database.php';

$database = new Database();
$conn = $database->connect();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	header("Location: ../login.php");
	exit();
}

$id = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$id) {
	header("Location: index.php");
	exit();
}

$stmt = $conn->prepare("SELECT * FROM links WHERE id = ?");
$stmt->execute([$id]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$link) {
	header("Location: index.php");
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$title = $_POST['title'] ?? '';
	$url = $_POST['url'] ?? '';

	if (empty($title) || empty($url)) {
		$error = 'Please fill in all fields.';
	} else {
		$stmt = $conn->prepare("UPDATE links SET title = ?, url = ? WHERE id = ?");
		if ($stmt->execute([$title, $url, $id])) {
			$success = 'Link updated successfully!';
			$stmt = $conn->prepare("SELECT * FROM links WHERE id = ?");
			$stmt->execute([$id]);
			$link = $stmt->fetch(PDO::FETCH_ASSOC);
			
			header("refresh:1;url=index.php");
		} else {
			$error = 'Failed to update link.';
		}
	}
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Edit Link - Linker</title>
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
							<a href="../logout.php" class="text-orange-500 border border-orange-500 hover:text-white hover:bg-orange-500 rounded px-4 py-1 text-sm font-bold transition">Logout</a>
						<?php else: ?>
							<a href="login.php" class="text-orange-500 border border-orange-500 hover:text-white hover:bg-orange-500 rounded px-4 py-1 text-sm font-bold transition">Login</a>
							<a href="register.php" class="bg-orange-500 text-white border border-orange-500 hover:bg-neutral-800/80 hover:border-orange-500 hover:text-orange-500 rounded px-4 py-1 text-sm font-bold transition">Register</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</nav>

        <main class="max-w-2xl mx-auto mt-20 px-4">
			<div class="bg-neutral-800 shadow-lg rounded-2xl border border-white/10 p-8 max-w-md mx-auto">
				<div class="text-center mb-6">
					<div class="flex items-center justify-center gap-3 mb-2">
						<svg class="w-10 h-10 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
							<path d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z"/>
						</svg>
						<h1 class="text-3xl font-bold text-white">Edit <span class="text-orange-500">Link</span></h1>
					</div>
				</div>

				<?php if ($error): ?>
					<div class="mb-4 p-3 bg-red-500/20 border border-red-500 rounded text-red-500 text-sm">
						<?php echo htmlspecialchars($error); ?>
					</div>
				<?php endif; ?>

				<?php if ($success): ?>
					<div class="mb-4 p-3 bg-green-500/20 border border-green-500 rounded text-green-500 text-sm">
						<?php echo htmlspecialchars($success); ?>
					</div>
				<?php endif; ?>

				<form method="POST" class="space-y-4">
					<div>
						<label for="title" class="block text-white/90 font-medium mb-2">Name:</label>
						<input type="text" id="title" name="title" value="<?php echo htmlspecialchars($link['title'] ?? ''); ?>" placeholder="Type your username..." class="w-full px-4 py-2 bg-neutral-700 border border-white/10 rounded text-white placeholder-white/40 focus:outline-none focus:border-orange-500" required />
					</div>

					<div>
						<label for="url" class="block text-white/90 font-medium mb-2">URL:</label>
						<input type="url" id="url" name="url" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" placeholder="Type your e-mail..." class="w-full px-4 py-2 bg-neutral-700 border border-white/10 rounded text-white placeholder-white/40 focus:outline-none focus:border-orange-500" required />
					</div>

					<button type="submit" class="w-full bg-orange-500 text-white hover:bg-orange-600 rounded py-2 font-bold transition mt-6">
						Update
					</button>
				</form>
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
