<?php
session_start();
require_once '../classes/Database.php';

// database connecten
$database = new Database();
$conn = $database->connect();

// controleren of admin is
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	header("Location: ../login.php");
	exit();
}

// links ophalen
$links = $conn->prepare("SELECT l.*, COUNT(c.id) as clicks FROM links l LEFT JOIN clicks c ON l.id = c.link_id GROUP BY l.id ORDER BY l.created_at DESC");
$links->execute();
$links = $links->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Admin Dashboard - Linker</title>
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

        <main class="max-w-5xl mx-auto mt-10">
			<div class="mb-8 flex items-center justify-between">
				<h1 class="text-2xl font-bold text-white">Admin | All Links</h1>

				<button onclick="window.location.href='users.php'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 font-bold transition">
					View Users
				</button>
			</div>

            <div class="relative overflow-x-auto bg-neutral-800 shadow-lg rounded-lg border border-white/10">
            <table class="w-full text-sm text-left text-white/80">
                <thead class="text-xs text-white/90 bg-neutral-700 border-b border-white/10">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium">Link name</th>
                    <th scope="col" class="px-6 py-3 font-medium">Link</th>
					<th scope="col" class="px-6 py-3 font-medium">Owner</th>
                    <th scope="col" class="px-6 py-3 font-medium">Clicks</th>
                    <th scope="col" class="px-6 py-3 font-medium">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($links as $link): ?>
                <tr class="bg-neutral-800 border-b border-white/10">
                    <td class="px-6 py-4"><?php echo htmlspecialchars($link['title']); ?></td>
                    <td class="px-6 py-4 max-w-xs">
						<a href="<?php echo htmlspecialchars($link['url']); ?>" 
						   class="text-orange-500 hover:underline block truncate" 
						   title="<?php echo htmlspecialchars($link['url']); ?>">
							<?php echo htmlspecialchars($link['url']); ?>
						</a>
					</td>
					<td class="px-6 py-4">
						<a href="edit_user.php?id=<?php echo htmlspecialchars($link['owner_id']); ?>" class="text-orange-500 hover:underline">
							<?php 
							$user = $conn->prepare("SELECT username FROM users WHERE id = ?");
							$user->execute([$link['owner_id']]);
							$userData = $user->fetch(PDO::FETCH_ASSOC);
							echo htmlspecialchars($userData['username'] ?? 'Unknown');
							?>
						</a>
					</td>
					<td class="px-6 py-4"><?php echo $link['clicks']; ?></td>
                    <td class="px-6 py-4 flex gap-3">
						<a href="edit_link.php?id=<?php echo htmlspecialchars($link['id']); ?>"><img src="../assets/svg/edit.svg" alt="Edit" class="w-5 h-5 cursor-pointer hover:opacity-75" /></a>
						<a href="delete_link.php?id=<?php echo htmlspecialchars($link['id']); ?>" onclick="return confirm('Are you sure you want to delete this link?');"><img src="../assets/svg/delete.svg" alt="Delete" class="w-5 h-5 cursor-pointer hover:opacity-75" /></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
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
