<?php
require_once "../db_connection.php";

// Query ongoing sessions
$query = "SELECT user_id, room_name, started_at FROM speaking_sessions WHERE status='ongoing'";
$result = $conn->query($query);

if ($result->num_rows > 0): ?>
    <h3>Ongoing Speaking Tests</h3>
    <ul>
    <?php while($row = $result->fetch_assoc()): ?>
        <li>
            User ID: <?= htmlspecialchars($row['user_id']) ?> 
            started at <?= $row['started_at'] ?>
            <a href="admin_speaking.php?uid=<?= htmlspecialchars($row['user_id']) ?>" target="_blank">
                Join Call
            </a>
        </li>
    <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No ongoing speaking tests right now.</p>
<?php endif; ?>
