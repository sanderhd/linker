<?php
session_start();
require_once '../functions/db_connection.php';

$userId = $_SESSION['user_id'] ?? null;
$id = $_GET['id'] ?? null;
$rangeKey = $_GET['range'] ?? '30d';

$rangeOptions = [
	'7d' => ['label' => 'Last 7 days', 'days' => 7],
	'30d' => ['label' => 'Last 30 days', 'days' => 30],
	'90d' => ['label' => 'Last 90 days', 'days' => 90],
	'all' => ['label' => 'All time', 'days' => null],
];

if (!isset($rangeOptions[$rangeKey])) {
	$rangeKey = '30d';
}

$rangeDays = $rangeOptions[$rangeKey]['days'];
$rangeStart = null;
if ($rangeDays !== null) {
	$rangeStart = new DateTime('today');
	$rangeStart->modify('-' . ($rangeDays - 1) . ' day');
}

$userLinks = [];
if ($userId) {
	$stmt = $conn->prepare("SELECT id, title FROM links WHERE owner_id = ? ORDER BY created_at DESC");
	$stmt->execute([$userId]);
	$userLinks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

if (!$id && !empty($userLinks)) {
	$id = $userLinks[0]['id'];
}

$link = null;
if ($id && $userId) {
	$stmt = $conn->prepare("SELECT * FROM links WHERE id = ? AND owner_id = ?");
	$stmt->execute([$id, $userId]);
	$link = $stmt->fetch(PDO::FETCH_ASSOC);
}

$clicks = [];
if ($id) {
	$clickQuery = "SELECT * FROM clicks WHERE link_id = ?";
	$clickParams = [$id];
	if ($rangeStart) {
		$clickQuery .= " AND date >= ?";
		$clickParams[] = $rangeStart->format('Y-m-d 00:00:00');
	}
	$clickQuery .= " ORDER BY date ASC";
	$stmt = $conn->prepare($clickQuery);
	$stmt->execute($clickParams);
	$clicks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$totalClicks = count($clicks);
$clickCounts = [];
foreach ($clicks as $click) {
	$clickedAt = $click['date'] ?? $click['clicked_at'] ?? null;
	if (!$clickedAt) {
		continue;
	}
	$bucket = date('Y-m-d', strtotime($clickedAt));
	$clickCounts[$bucket] = ($clickCounts[$bucket] ?? 0) + 1;
}

if (!empty($clickCounts)) {
	ksort($clickCounts);
	$dates = array_keys($clickCounts);
	$start = $rangeStart ? clone $rangeStart : new DateTime(reset($dates));
	$end = new DateTime('today');
	if ($end < $start) {
		$end = new DateTime(end($dates));
	}
	$end->modify('+1 day');

	$period = new DatePeriod($start, new DateInterval('P1D'), $end);
	$filledCounts = [];
	foreach ($period as $date) {
		$label = $date->format('Y-m-d');
		$filledCounts[$label] = $clickCounts[$label] ?? 0;
	}
	$clickCounts = $filledCounts;
}

$clickLabels = array_keys($clickCounts);
$clickData = array_values($clickCounts);

$topOs = [];
$topLocations = [];
$topDevices = [];

$rangeSql = $rangeStart ? " AND date >= ?" : "";
$rangeParams = $rangeStart ? [$rangeStart->format('Y-m-d 00:00:00')] : [];

if ($id) {
	$stmt = $conn->prepare("SELECT operating_system AS label, COUNT(*) AS total FROM clicks WHERE link_id = ?" . $rangeSql . " AND operating_system IS NOT NULL AND operating_system <> '' GROUP BY operating_system ORDER BY total DESC LIMIT 3");
	$stmt->execute(array_merge([$id], $rangeParams));
	$topOs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

	$stmt = $conn->prepare("SELECT location AS label, COUNT(*) AS total FROM clicks WHERE link_id = ?" . $rangeSql . " AND location IS NOT NULL AND location <> '' GROUP BY location ORDER BY total DESC LIMIT 3");
	$stmt->execute(array_merge([$id], $rangeParams));
	$topLocations = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

	$stmt = $conn->prepare("SELECT device AS label, COUNT(*) AS total FROM clicks WHERE link_id = ?" . $rangeSql . " AND device IS NOT NULL AND device <> '' GROUP BY device ORDER BY total DESC LIMIT 3");
	$stmt->execute(array_merge([$id], $rangeParams));
	$topDevices = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$trendPercent = null;
$trendDirection = null;
if ($id) {
	$stmt = $conn->prepare("SELECT COUNT(*) FROM clicks WHERE link_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
	$stmt->execute([$id]);
	$last7 = (int) $stmt->fetchColumn();

	$stmt = $conn->prepare("SELECT COUNT(*) FROM clicks WHERE link_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
	$stmt->execute([$id]);
	$prev7 = (int) $stmt->fetchColumn();

	if ($prev7 > 0) {
		$trendPercent = round((($last7 - $prev7) / $prev7) * 100);
		$trendDirection = $trendPercent >= 0 ? 'up' : 'down';
	} elseif ($last7 > 0) {
		$trendPercent = 100;
		$trendDirection = 'up';
	}
}

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

		<main class="max-w-5xl mx-auto mt-10 mb-10">
			<div class="mb-6">
				<div class="flex flex-wrap items-center justify-between gap-4">
					<h1 class="text-2xl font-bold text-white">Analytics | <?php echo htmlspecialchars($link['title'] ?? ''); ?></h1>
					<button onclick="window.location.href='index.php'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 inline-flex items-center gap-2 font-bold transition">
						My links
					</button>
				</div>

				<form method="get" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
					<div>
						<label for="linkSelect" class="block text-xs text-white/50 mb-1">Your links</label>
						<select id="linkSelect" name="id" class="w-full bg-neutral-800/80 border border-white/10 rounded-xl px-3 py-2 text-white" onchange="this.form.submit()">
							<?php if (!empty($userLinks)): ?>
								<?php foreach ($userLinks as $userLink): ?>
									<option value="<?php echo (int) $userLink['id']; ?>" <?php echo ((string) $userLink['id'] === (string) $id) ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($userLink['title'] ?: 'Untitled link'); ?>
									</option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value="">No links found</option>
							<?php endif; ?>
						</select>
					</div>

					<div>
						<label for="rangeSelect" class="block text-xs text-white/50 mb-1">Range</label>
						<select id="rangeSelect" name="range" class="w-full bg-neutral-800/80 border border-white/10 rounded-xl px-3 py-2 text-white" onchange="this.form.submit()">
							<?php foreach ($rangeOptions as $key => $option): ?>
								<option value="<?php echo htmlspecialchars($key); ?>" <?php echo $key === $rangeKey ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($option['label']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</form>
			</div>

				<?php if ($totalClicks > 0): ?>
					<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
						<div class="bg-neutral-800/80 border border-white/10 rounded-2xl p-4 lg:col-span-2">
							<div class="flex items-center justify-between mb-2">
								<h3 class="text-white font-bold">Views per day</h3>
								<span class="text-white/40 text-xs">Daily</span>
							</div>
							<div class="h-48">
								<canvas id="clicksChart" class="w-full h-full"></canvas>
							</div>
						</div>

						<div class="bg-neutral-800/80 border border-white/10 rounded-2xl p-4">
							<h3 class="text-white font-bold">Top OS</h3>
							<ul class="mt-3 text-white/80 text-sm space-y-2">
								<?php if (!empty($topOs)): ?>
									<?php foreach ($topOs as $row): ?>
										<li><?php echo htmlspecialchars($row['label']); ?> <span class="text-white/40">→</span> <?php echo (int) $row['total']; ?></li>
									<?php endforeach; ?>
								<?php else: ?>
									<li class="text-white/50">No data</li>
								<?php endif; ?>
							</ul>
						</div>

						<div class="bg-neutral-800/80 border border-white/10 rounded-2xl p-4">
							<div class="flex items-center justify-between">
								<h3 class="text-white font-bold">Total clicks</h3>
							</div>
							<p class="mt-6 text-4xl font-bold text-white"><?php echo $totalClicks; ?></p>
							<?php if ($trendPercent !== null): ?>
								<p class="mt-2 text-sm <?php echo $trendDirection === 'down' ? 'text-red-400' : 'text-green-400'; ?>">
									<?php echo $trendDirection === 'down' ? '↓' : '↑'; ?> <?php echo abs($trendPercent); ?>%
								</p>
							<?php endif; ?>
						</div>

						<div class="bg-neutral-800/80 border border-white/10 rounded-2xl p-4">
							<h3 class="text-white font-bold">Top locations</h3>
							<ul class="mt-3 text-white/80 text-sm space-y-2">
								<?php if (!empty($topLocations)): ?>
									<?php foreach ($topLocations as $row): ?>
										<li><?php echo htmlspecialchars($row['label']); ?> <span class="text-white/40">→</span> <?php echo (int) $row['total']; ?></li>
									<?php endforeach; ?>
								<?php else: ?>
									<li class="text-white/50">No data</li>
								<?php endif; ?>
							</ul>
						</div>

						<div class="bg-neutral-800/80 border border-white/10 rounded-2xl p-4">
							<h3 class="text-white font-bold">Top devices</h3>
							<ul class="mt-3 text-white/80 text-sm space-y-2">
								<?php if (!empty($topDevices)): ?>
									<?php foreach ($topDevices as $row): ?>
										<li><?php echo htmlspecialchars($row['label']); ?> <span class="text-white/40">→</span> <?php echo (int) $row['total']; ?></li>
									<?php endforeach; ?>
								<?php else: ?>
									<li class="text-white/50">No data</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				<?php else: ?>
					<p class="text-white/90">No clicks yet.</p>
				<?php endif; ?>
        </main>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"></script>

		<?php if ($totalClicks > 0): ?>
			<script>
				const ctx = document.getElementById('clicksChart');
				const clicksLabels = <?php echo json_encode($clickLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
				const clicksData = <?php echo json_encode($clickData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

				if (ctx) {
					new Chart(ctx, {
						type: 'line',
						data: {
							labels: clicksLabels,
							datasets: [{
								label: 'Clicks per day',
								data: clicksData,
								borderColor: '#f97316',
								backgroundColor: 'rgba(249, 115, 22, 0.2)',
								fill: true,
								tension: 0.35,
								pointRadius: 2,
								pointHoverRadius: 4
							}]
						},
						options: {
							maintainAspectRatio: false,
							plugins: {
								legend: { display: false }
							},
							scales: {
								x: {
								grid: { color: 'rgba(255, 255, 255, 0.08)' },
								ticks: { color: 'rgba(255, 255, 255, 0.6)', maxTicksLimit: 10 }
							},
								y: {
								beginAtZero: true,
								grid: { color: 'rgba(255, 255, 255, 0.08)' },
								ticks: { color: 'rgba(255, 255, 255, 0.6)' }
							}
						}
					}
					});
				}
			</script>
		<?php endif; ?>
	</body>
</html>
