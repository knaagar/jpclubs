<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>jpclubs</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
    <div>
        <h1>jpclubs</h1>
        <small>All the events - in one place!</small>
    </div>
    <nav>
        <a href="<?= isset($_SESSION['club_id']) ? '/dashboard' : '/login' ?>">
            <?= isset($_SESSION['club_id']) ? 'Club Dashboard' : 'Club Admin Login' ?>
        </a>
    </nav>
</header>

<main>
    <div class="filters">
        <input type="text" id="searchInput" placeholder="Search events, venues, keywords...">
        <select id="clubFilter">
            <option value="">All Clubs</option>
            <?php foreach ($clubs as $c): ?>
                <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="categoryFilter">
            <option value="">All Categories</option>
            <option value="Workshop">Workshop</option>
            <option value="Hackathon">Hackathon</option>
            <option value="Competition">Competition</option>
            <option value="Cultural">Cultural</option>
            <option value="Talk / Seminar">Talk / Seminar</option>
        </select>
        <input type="date" id="dateFilter" title="Filter events on or after this date">
    </div>

    <div id="eventsGrid" class="grid"></div>
</main>

<dialog id="eventModal">
    <img id="modalPoster" src="" alt="Event Poster">
    <p><strong id="modalCategory"></strong> &bull; <span id="modalClub"></span></p>
    <h2 id="modalTitle"></h2>
    <p id="modalDesc"></p>
    <p>
        <strong>Date:</strong> <span id="modalDate"></span><br>
        <strong>Venue:</strong> <span id="modalVenue"></span><br>
        <strong>Register By:</strong> <span id="modalDeadline"></span>
    </p>
    <a id="modalRegisterLink" href="#" target="_blank" rel="noopener noreferrer" class="btn">Register for Event</a>
    <button type="button" id="closeModal">Close</button>
</dialog>

<script>
    let allEvents = [];
    const grid = document.getElementById('eventsGrid');
    const modal = document.getElementById('eventModal');
    const searchInput = document.getElementById('searchInput');
    const clubFilter = document.getElementById('clubFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const dateFilter = document.getElementById('dateFilter');

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        return new Date(dateStr.replace(' ', 'T')).toLocaleString('en-IN', {
            dateStyle: 'medium', timeStyle: 'short'
        });
    }

    function renderEvents(events) {
        if (!events.length) {
            grid.innerHTML = '<p>No matching events found.</p>';
            return;
        }

        grid.innerHTML = events.map(ev => `
            <article onclick="openModal(${ev.id})">
                <img src="${escapeHtml(ev.poster || 'https://placehold.co/600x360/fbe3c8/78350f?text=JIIT+Event')}" alt="Poster">
                <h3>${escapeHtml(ev.title)}</h3>
                <p><strong>${escapeHtml(ev.category)}</strong> &bull; ${escapeHtml(ev.club_name)}</p>
                <small>${formatDate(ev.event_date)} | ${escapeHtml(ev.venue)}</small>
            </article>
        `).join('');
    }

    function applyFilters() {
        const q = searchInput.value.toLowerCase().trim();
        const club = clubFilter.value;
        const cat = categoryFilter.value;
        const minDate = dateFilter.value;

        const filtered = allEvents.filter(ev => {
            const matchesQuery = !q ||
                ev.title.toLowerCase().includes(q) ||
                ev.description.toLowerCase().includes(q) ||
                ev.venue.toLowerCase().includes(q);
            const matchesClub = !club || String(ev.club_id) === club;
            const matchesCat = !cat || ev.category === cat;
            const matchesDate = !minDate || ev.event_date.slice(0, 10) >= minDate;
            return matchesQuery && matchesClub && matchesCat && matchesDate;
        });

        renderEvents(filtered);
    }

    function openModal(id) {
        const ev = allEvents.find(item => Number(item.id) === Number(id));
        if (!ev) return;

        document.getElementById('modalPoster').src = ev.poster || 'https://placehold.co/600x360/fbe3c8/78350f?text=JIIT+Event';
        document.getElementById('modalCategory').textContent = ev.category;
        document.getElementById('modalTitle').textContent = ev.title;
        document.getElementById('modalClub').textContent = ev.club_name;
        document.getElementById('modalDesc').textContent = ev.description;
        document.getElementById('modalDate').textContent = formatDate(ev.event_date);
        document.getElementById('modalVenue').textContent = ev.venue;
        document.getElementById('modalDeadline').textContent = formatDate(ev.deadline);
        document.getElementById('modalRegisterLink').href = ev.registration_link;

        modal.showModal();
    }

    document.getElementById('closeModal').addEventListener('click', () => modal.close());
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.close();
    });

    [searchInput, clubFilter, categoryFilter, dateFilter].forEach(el => {
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
    });

    fetch('/api/events')
        .then(res => res.json())
        .then(data => {
            allEvents = data;
            renderEvents(allEvents);
        });
</script>
</body>
</html>