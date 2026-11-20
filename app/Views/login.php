<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Admin Login | JIIT Events</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
    <div>
        <h1>jpclubs</h1>
        <small>All the events - in one place!</small>
    </div>
    <nav>
        <a href="/">Back to Event Feed</a>
    </nav>
</header>

<main class="narrow">
    <section>
        <h2>Club Head Login</h2>
        <?php if (!empty($error)): ?>
            <p class="notice"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="/login">
            <label for="username">Club Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Sign In</button>
        </form>
    </section>
</main>
</body>
</html>