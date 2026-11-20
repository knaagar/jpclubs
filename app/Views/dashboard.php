<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_SESSION['club_name']) ?> Dashboard | jpclubs</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
    <div>
        <h1><?= htmlspecialchars($_SESSION['club_name']) ?></h1>
        <small>Dashboard</small>
    </div>
    <nav>
        <a href="/">Public Feed</a>
        <form method="POST" action="/logout">
            <button type="submit">Logout</button>
        </form>
    </nav>
</header>

<main>
    <?php if (isset($_GET['status'])): ?>
        <p class="notice">Event successfully <?= htmlspecialchars($_GET['status']) ?>.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="notice"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <section>
        <h2><?= $editingEvent ? 'Edit Event' : 'Publish New Event' ?></h2>
        <form method="POST" action="<?= $editingEvent ? '/events/update' : '/events/create' ?>" enctype="multipart/form-data">
            <?php if ($editingEvent): ?>
                <input type="hidden" name="id" value="<?= (int) $editingEvent['id'] ?>">
            <?php endif; ?>

            <label>Event Title</label>
            <input type="text" name="title" required value="<?= htmlspecialchars($editingEvent['title'] ?? '') ?>">

            <label>Category</label>
            <select name="category" required>
                <?php
                $categories = ['Workshop', 'Hackathon', 'Competition', 'Cultural', 'Talk / Seminar'];
                $selectedCat = $editingEvent['category'] ?? '';
                foreach ($categories as $cat):
                ?>
                    <option value="<?= $cat ?>" <?= $selectedCat === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>

            <label>Description</label>
            <textarea name="description" required><?= htmlspecialchars($editingEvent['description'] ?? '') ?></textarea>

            <label>Event Date & Time</label>
            <input type="datetime-local" name="event_date" required
                   value="<?= $editingEvent ? date('Y-m-d\TH:i', strtotime($editingEvent['event_date'])) : '' ?>">

            <label>Venue (e.g., LT-4, Sector 62 / Auditorium)</label>
            <input type="text" name="venue" required value="<?= htmlspecialchars($editingEvent['venue'] ?? '') ?>">

            <label>Registration Form Link</label>
            <input type="url" name="registration_link" placeholder="https://forms.gle/..." required
                   value="<?= htmlspecialchars($editingEvent['registration_link'] ?? '') ?>">

            <label>Registration Deadline</label>
            <input type="datetime-local" name="deadline" required
                   value="<?= $editingEvent ? date('Y-m-d\TH:i', strtotime($editingEvent['deadline'])) : '' ?>">

            <label>Event Poster (JPG, PNG, WebP - Max 5MB) <?= $editingEvent ? '(Leave empty to keep current)' : '' ?></label>
            <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">

            <button type="submit"><?= $editingEvent ? 'Save Changes' : 'Publish Event' ?></button>
            <?php if ($editingEvent): ?>
                <a href="/dashboard">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Published Events (<?= count($events) ?>)</h2>
        <?php if (empty($events)): ?>
            <p>No events published yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Event</th>
                        <th>Date & Venue</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($ev['poster'] ?: 'https://placehold.co/150x150/fbe3c8/78350f?text=Event') ?>" alt="Poster"></td>
                            <td>
                                <strong><?= htmlspecialchars($ev['title']) ?></strong><br>
                                <small><?= htmlspecialchars($ev['category']) ?></small>
                            </td>
                            <td>
                                <?= date('M d, Y h:i A', strtotime($ev['event_date'])) ?><br>
                                <small><?= htmlspecialchars($ev['venue']) ?></small>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($ev['deadline'])) ?></td>
                            <td>
                                <a href="/dashboard?edit=<?= (int) $ev['id'] ?>">Edit</a>
                                <form method="POST" action="/events/delete" onsubmit="return confirm('Delete this event permanently?');">
                                    <input type="hidden" name="id" value="<?= (int) $ev['id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>