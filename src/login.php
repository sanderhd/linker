<?php

// Classes binnen halen
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Validator.php';
session_start();

// Aanmaken
$database = new Database();
$conn = $database->connect();
$userModel = new User($conn);

// Login form handleren
if(isset($_POST['login'])) {
	$username = Validator::sanitize($_POST['username'] ?? '');
	$password = $_POST['password'] ?? '';

	$user = $userModel->verifyPassword($username, $password);

	if($user) {
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['username'] = $user['username'];
		$_SESSION['role'] = $user['role'] ?? 'user';

		$dashboardUrl = ($_SESSION['role'] === 'admin') ? 'admin/index.php' : 'dashboard/index.php'; // sturen naar admin of normaal aan de hand vna de role
		header("Location: $dashboardUrl");
		exit();
	} else {
		$notification = ['error' => 'Invalid username or password.'];
	}
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Login - Link Tracker</title>
		<link rel="stylesheet" href="css/tailwind.css" />
		<link rel="shortcut icon" href="assets/link.png" type="image/x-icon">
	</head>
	<body class="bg-neutral-900 min-h-screen">
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

		<main class="flex items-center justify-center px-2 m-8" style="min-height: calc(100vh - 200px);">
			<div class="w-full max-w-md bg-neutral-800/50 border border-white/10 rounded-2xl px-12 py-10">
				<form action="login.php" method="POST">
					<h1 class="text-white text-xl font-normal mb-6 text-center">Login</h1>
					
					<a href="index.php" class="flex items-center justify-center gap-3 text-white mb-10">
						<svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
						</svg>
						<span class="font-bold text-2xl">Link <span class="text-orange-500">Tracker</span></span>
					</a>

					<?php
                        $notification ??= null;

                        if ($notification) {
                            $type = key($notification);
                            $message = $notification[$type];
                            $color = $type === 'success' ? 'green-600' : 'red-600';
                            $icon = $type === 'success' ? 'assets/svg/success.svg' : 'assets/svg/error.svg';
                    ?>
                            <div class='mb-4 px-4 py-3 rounded-lg bg-<?php echo $color; ?>/10 border border-<?php echo $color; ?> text-white flex items-center gap-3'>
                                <img src='<?php echo $icon; ?>' alt='<?php echo $type; ?>' class='w-5 h-5' />
                                <span class='text-center flex-1'><?php echo htmlspecialchars($message); ?></span>

                                <button type="button" onclick="this.parentElement.style.display='none'" class="text-white/60 hover:text-white transition">
                                    <img src='assets/svg/close.svg' alt='close' class='w-4 h-4' />
                                </button>
                            </div>
                    <?php
                        }
                    ?>

					<div class="mb-5">
						<label for="username" class="block mb-2 text-white font-semibold text-sm">Username:</label>
						<input 
							type="text" 
							id="username" 
							name="username" 
							placeholder="Type your name..." 
							class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition" 
							required 
						/>
					</div>
					
					<div class="mb-2 relative">
						<label for="password" class="block mb-2 text-white font-semibold text-sm">Password:</label>
						<input 
							type="password" 
							id="password" 
							name="password" 
							placeholder="Type your password..." 
							class="bg-neutral-900 border border-white/20 rounded-lg px-4 py-2.5 w-full text-white text-sm placeholder-white/30 focus:outline-none focus:border-orange-500 transition" 
							required 
						/>

						<button type="button" data-target="password" class="toggle-pass absolute right-3 top-10 text-white/40 hover:text-white transition">
							<img src="assets/svg/eye.svg" class="w-5 h-5" />
						</button>
					</div>
					
					<div class="text-right mb-8">
						<a href="#" class="text-orange-500 hover:underline text-xs">Forgot your password?</a>
					</div>

					<button 
						type="submit" 
						name="login"
						class="bg-transparent text-orange-500 rounded-full px-4 py-2.5 w-full font-semibold border border-orange-500 hover:bg-orange-500 hover:text-white transition cursor-pointer mb-5"
					>
						Log in
					</button>
					
					<p class="text-sm text-center text-white/40">Not a member? <a href="register.php" class="text-orange-500 hover:underline">Sign up now!</a></p>
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
			document.querySelectorAll('.toggle-pass').forEach(btn => {
				btn.addEventListener('click', () => {
					const targetId = btn.getAttribute('data-target');
					const input    = document.getElementById(targetId);

					const isHidden = input.type === 'password';
					input.type     = isHidden ? 'text' : 'password';

					btn.innerHTML = isHidden
						? '<img src="assets/svg/eye-no.svg" class="w-5 h-5" />'
						: '<img src="assets/svg/eye.svg" class="w-5 h-5" />';
				});
			});
		</script>
	</body>
</html>