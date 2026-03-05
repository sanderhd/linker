<?php
session_start();
require_once 'functions/db_connection.php';

$sql = "SELECT 
            l.id,
            l.title,
            l.url,
            l.owner_id,
            u.username,
            COUNT(c.id) as click_count
        FROM links l
        LEFT JOIN clicks c ON l.id = c.link_id
        LEFT JOIN users u ON l.owner_id = u.id
        GROUP BY l.id
        ORDER BY click_count DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->execute();
$topLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
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

        <main class="text-white mx-auto mt-20 max-w-6xl px-4">
            <div class="text-center mb-16">
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold">
                    View our <span class="text-orange-500">best</span> performing<br>
                    <span class="text-orange-500">links</span>
                </h1>
            </div>

            <div class="bg-neutral-800/40 border border-white/10 rounded-2xl overflow-hidden mb-20">
                <table class="w-full">
                    <thead class="border-b border-white/10">
                        <tr class="text-left">
                            <th class="px-6 py-4 font-semibold text-white/90">Link name</th>
                            <th class="px-6 py-4 font-semibold text-white/90">Link</th>
                            <th class="px-6 py-4 font-semibold text-white/90">Clicks</th>
                            <th class="px-6 py-4 font-semibold text-white/90">Owner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topLinks)): ?>
                            <?php foreach ($topLinks as $row): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="px-6 py-4 text-white/90"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="px-6 py-4 text-white/70"><?php echo htmlspecialchars($row['url']); ?></td>
                            <td class="px-6 py-4 text-white/90"><?php echo $row['click_count']; ?></td>
                            <td class="px-6 py-4 text-white/90"><?php echo htmlspecialchars($row['username']); ?></td>
                        </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-white/50">No links found</td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center">
                                <div class="flex items-center justify-center gap-2 text-white/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    <span>And many more. <a href="register.php" class="text-orange-500 hover:underline">Join us today</a></span>
                                </div>
                            </td>
                        </tr>
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
