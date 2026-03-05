<?php
session_start();
require_once '../functions/db_connection.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	header("Location: ../login.php");
	exit();
}

// Fetch all users
$users = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
$users->execute();
$users = $users->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Admin - Users - Linker</title>
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

					<a href="../links.php" class="absolute left-1/2 transform -translate-x-1/2 text-white/90 font-medium pointer-events-auto hover:underline hover:text-orange-500 transition">Links</a>
					
					<div class="flex items-center gap-3">
						<a href="../logout.php" class="bg-orange-500 text-white hover:bg-orange-600 rounded px-4 py-1 text-sm font-bold transition">Log out</a>
					</div>
				</div>
			</div>
		</nav>

        <main class="max-w-5xl mx-auto mt-10">
			<div class="mb-8 flex items-center justify-between">
				<h1 class="text-2xl font-bold text-white">Admin | All Users</h1>

				<button onclick="window.location.href='index.php'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 font-bold transition">
					View Links
				</button>
			</div>

            <div class="relative overflow-x-auto bg-neutral-800 shadow-lg rounded-lg border border-white/10">
            <table class="w-full text-sm text-left text-white/80">
                <thead class="text-xs text-white/90 bg-neutral-700 border-b border-white/10">
                <tr>
                    <th scope="col" class="px-6 py-3 font-medium">Username</th>
                    <th scope="col" class="px-6 py-3 font-medium">Email</th>
                    <th scope="col" class="px-6 py-3 font-medium">Role</th>
                    <th scope="col" class="px-6 py-3 font-medium">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="bg-neutral-800 border-b border-white/10">
                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['role']); ?></td>
                    <td class="px-6 py-4 flex gap-3">
						<a href="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>"><img src="../assets/svg/edit.svg" alt="Edit" class="w-5 h-5 cursor-pointer hover:opacity-75" /></a>
						<a href="delete_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" onclick="return confirm('Are you sure you want to delete this user?');"><img src="../assets/svg/delete.svg" alt="Delete" class="w-5 h-5 cursor-pointer hover:opacity-75" /></a>
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
