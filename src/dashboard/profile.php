<?php
session_start();
require_once '../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
	header('Location: ../login.php');
	exit();
}

$userId = (int) $_SESSION['user_id'];
$alertMessage = null;
$newApiKey = null;

function profileNotification(string $message): string
{
	return $message;
}

function generateApiKey(): string
{
	return 'lk_' . bin2hex(random_bytes(24));
}

$userStmt = $conn->prepare('SELECT id, username, password FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
	session_destroy();
	header('Location: ../login.php');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';

	if ($action === 'update_username') {
		$username = trim($_POST['username'] ?? '');

		if ($username === '') {
			$alertMessage = profileNotification('Username is required.');
		} elseif (strlen($username) < 3 || strlen($username) > 50) {
			$alertMessage = profileNotification('Username must be between 3 and 50 characters.');
		} else {
			$checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
			$checkStmt->execute([$username, $userId]);

			if ($checkStmt->fetch()) {
				$alertMessage = profileNotification('This username is already taken.');
			} else {
				$updateStmt = $conn->prepare('UPDATE users SET username = ? WHERE id = ?');
				$updateStmt->execute([$username, $userId]);
				$_SESSION['username'] = $username;
				$user['username'] = $username;
				$alertMessage = profileNotification('Username updated successfully.');
			}
		}
	}

	if ($action === 'update_password') {
		$currentPassword = $_POST['current_password'] ?? '';
		$newPassword = $_POST['new_password'] ?? '';
		$confirmPassword = $_POST['confirm_password'] ?? '';

		if (!password_verify($currentPassword, $user['password'])) {
			$alertMessage = profileNotification('Current password is incorrect.');
		} elseif (strlen($newPassword) < 8) {
			$alertMessage = profileNotification('New password must be at least 8 characters long.');
		} elseif ($newPassword !== $confirmPassword) {
			$alertMessage = profileNotification('New passwords do not match.');
		} else {
			$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
			$updateStmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
			$updateStmt->execute([$newPasswordHash, $userId]);
			$user['password'] = $newPasswordHash;
			$alertMessage = profileNotification('Password changed successfully.');
		}
	}

	if ($action === 'generate_api_key') {
		try {
			$plainKey = generateApiKey();
			$hashedKey = hash('sha256', $plainKey);
			$keyPrefix = substr($plainKey, 0, 12);

			$upsertStmt = $conn->prepare(
				'INSERT INTO api_keys (user_id, key_hash, key_prefix, revoked_at)
				 VALUES (?, ?, ?, NULL)
				 ON DUPLICATE KEY UPDATE key_hash = VALUES(key_hash), key_prefix = VALUES(key_prefix), revoked_at = NULL, updated_at = CURRENT_TIMESTAMP'
			);
			$upsertStmt->execute([$userId, $hashedKey, $keyPrefix]);

			$newApiKey = $plainKey;
			$alertMessage = profileNotification('New API key generated. Save it now, it is only shown once.');
		} catch (Throwable $e) {
			$alertMessage = profileNotification('Could not generate API key. Please try again.');
		}
	}

	if ($action === 'revoke_api_key') {
		$revokeStmt = $conn->prepare('UPDATE api_keys SET revoked_at = CURRENT_TIMESTAMP WHERE user_id = ? AND revoked_at IS NULL');
		$revokeStmt->execute([$userId]);
		$alertMessage = profileNotification('Active API key revoked.');
	}

	if ($action === 'delete_account') {
		$deleteConfirm = trim($_POST['delete_confirm'] ?? '');
		$deletePassword = $_POST['delete_password'] ?? '';

		if ($deleteConfirm !== 'DELETE') {
			$alertMessage = profileNotification('Type DELETE to confirm account deletion.');
		} elseif (!password_verify($deletePassword, $user['password'])) {
			$alertMessage = profileNotification('Password is incorrect. Account was not deleted.');
		} else {
			$deleteStmt = $conn->prepare('DELETE FROM users WHERE id = ?');
			$deleteStmt->execute([$userId]);

			session_unset();
			session_destroy();

			header('Location: ../index.php');
			exit();
		}
	}
}

$apiKeyStmt = $conn->prepare('SELECT key_prefix, created_at, updated_at, revoked_at FROM api_keys WHERE user_id = ? LIMIT 1');
$apiKeyStmt->execute([$userId]);
$apiKeyData = $apiKeyStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Profile - Link Tracker</title>
		<link rel="stylesheet" href="../css/tailwind.css" />
		<link rel="shortcut icon" href="../assets/link.png" type="image/x-icon">
	</head>
	<body class="bg-neutral-900 min-h-screen">
		<nav class="mx-4 mt-6">
			<div class="max-w-5xl mx-auto bg-neutral-800/80 border border-white/10 rounded-2xl px-4 py-3 relative">
				<div class="flex items-center justify-between gap-4">
					<a href="../index.php" class="flex items-center gap-3 text-white">
						<img src="../assets/link.png" alt="Logo" class="w-6 h-6" />
						<span class="font-semibold">Link <span class="text-orange-500">Tracker</span></span>
					</a>

					<a href="/links" class="absolute left-1/2 transform -translate-x-1/2 text-white/90 font-medium pointer-events-auto hover:underline hover:text-orange-500 transition">Links</a>

					<div class="flex items-center gap-3">
						<a href="profile.php"><span class="text-white/90 text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span></a>
						<a href="index.php" class="bg-orange-500 text-white hover:bg-orange-600 rounded px-4 py-1 text-sm font-bold transition">Dashboard</a>
						<a href="../logout.php" class="text-orange-500 border border-orange-500 hover:text-white hover:bg-orange-500 rounded px-4 py-1 text-sm font-bold transition">Logout</a>
					</div>
				</div>
			</div>
		</nav>

		<main class="max-w-5xl mx-auto mt-10 px-4 pb-16">
			<div class="mb-8 flex items-center justify-between">
				<h1 class="text-2xl font-bold text-white">Edit Profile</h1>

				<button onclick="window.location.href='index.php'" class="text-white bg-orange-500 hover:bg-orange-600 rounded px-4 py-2 inline-flex items-center gap-2 font-bold transition">
					My links
				</button>
			</div>

			<?php if ($newApiKey): ?>
				<div class="mb-8 rounded-lg border border-orange-500 bg-orange-500/10 p-4">
					<p class="text-white font-semibold mb-2">New API Key</p>
					<code class="block break-all rounded bg-neutral-900 px-3 py-2 text-orange-400 text-sm"><?php echo htmlspecialchars($newApiKey); ?></code>
					<p class="text-xs text-white/70 mt-2">This key is only fully visible on this page load.</p>
				</div>
			<?php endif; ?>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
				<section class="bg-neutral-800/50 border border-white/10 rounded-2xl p-6">
					<h2 class="text-white text-lg font-semibold mb-4">Username</h2>
					<form action="profile.php" method="POST" class="space-y-4">
						<input type="hidden" name="action" value="update_username" />

						<div>
							<label for="username" class="block mb-2 text-white font-semibold text-sm">New username</label>
							<input
								type="text"
								id="username"
								name="username"
								value="<?php echo htmlspecialchars($user['username']); ?>"
								class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition"
								required
							/>
						</div>

						<button type="submit" class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer">
							Update username
						</button>
					</form>
				</section>

				<section class="bg-neutral-800/50 border border-white/10 rounded-2xl p-6">
					<h2 class="text-white text-lg font-semibold mb-4">Password</h2>
					<form action="profile.php" method="POST" class="space-y-4">
						<input type="hidden" name="action" value="update_password" />

						<div>
							<label for="current_password" class="block mb-2 text-white font-semibold text-sm">Current password</label>
							<input
								type="password"
								id="current_password"
								name="current_password"
								class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition"
								required
							/>
						</div>

						<div>
							<label for="new_password" class="block mb-2 text-white font-semibold text-sm">New password</label>
							<input
								type="password"
								id="new_password"
								name="new_password"
								class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition"
								required
							/>
						</div>

						<div>
							<label for="confirm_password" class="block mb-2 text-white font-semibold text-sm">Confirm new password</label>
							<input
								type="password"
								id="confirm_password"
								name="confirm_password"
								class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition"
								required
							/>
						</div>

						<button type="submit" class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer">
							Change password
						</button>
					</form>
				</section>

				<section class="bg-neutral-800/50 border border-white/10 rounded-2xl p-6">
					<h2 class="text-white text-lg font-semibold mb-4">API key</h2>

					<?php if ($apiKeyData): ?>
						<div class="text-sm text-white/80 mb-4 space-y-1">
							<p>Prefix: <span class="text-orange-400"><?php echo htmlspecialchars($apiKeyData['key_prefix']); ?>...</span></p>
							<p>Status: <?php echo $apiKeyData['revoked_at'] ? 'Revoked' : 'Active'; ?></p>
							<p>Created: <?php echo htmlspecialchars($apiKeyData['created_at']); ?></p>
						</div>
					<?php else: ?>
						<p class="text-sm text-white/70 mb-4">No API key created yet.</p>
					<?php endif; ?>

					<div class="flex flex-col gap-3">
						<form action="profile.php" method="POST">
							<input type="hidden" name="action" value="generate_api_key" />
							<button type="submit" class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer">
								Generate new API key
							</button>
						</form>

						<form action="profile.php" method="POST" onsubmit="return confirm('Revoke active API key?');">
							<input type="hidden" name="action" value="revoke_api_key" />
							<button type="submit" class="bg-transparent text-white rounded-full px-4 py-2.5 w-full font-semibold border border-white/30 hover:bg-white/10 transition cursor-pointer">
								Revoke API key
							</button>
						</form>
					</div>
				</section>

				<section class="bg-red-900/20 border border-red-600/40 rounded-2xl p-6">
					<h2 class="text-white text-lg font-semibold mb-2">Delete account</h2>
					<p class="text-sm text-white/70 mb-4">This permanently deletes your account and all links.</p>

					<form action="profile.php" method="POST" class="space-y-4" onsubmit="return confirm('Are you sure you want to delete your account?');">
						<input type="hidden" name="action" value="delete_account" />

						<div>
							<label for="delete_confirm" class="block mb-2 text-white font-semibold text-sm">Type DELETE to confirm</label>
							<input
								type="text"
								id="delete_confirm"
								name="delete_confirm"
								placeholder="DELETE"
								class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-red-500 transition"
								required
							/>
						</div>

						<div>
							<label for="delete_password" class="block mb-2 text-white font-semibold text-sm">Password</label>
							<input
								type="password"
								id="delete_password"
								name="delete_password"
								class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-red-500 transition"
								required
							/>
						</div>

						<button type="submit" class="bg-transparent text-red-400 rounded-full px-4 py-2.5 w-full font-semibold border border-red-500 hover:bg-red-500 hover:text-white transition cursor-pointer">
							Delete account
						</button>
					</form>
				</section>
			</div>
		</main>

		<footer class="bg-neutral-900 mt-auto">
			<div class="max-w-5xl mx-auto mb-6 text-center text-white/50 text-sm">
				<div class="h-px bg-white/10 mb-4"></div>
				&copy; 2026 Link Tracker. All rights reserved.
			</div>
		</footer>

		<?php if ($alertMessage): ?>
			<script>
				alert(<?php echo json_encode($alertMessage); ?>);
			</script>
		<?php endif; ?>
	</body>
</html>