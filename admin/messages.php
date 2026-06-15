<?php
session_start();
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

// Mark as read
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE contact_messages SET is_read=1 WHERE id=$id");
    header("Location: messages.php");
    exit();
}

// Delete message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM contact_messages WHERE id=$id");
    header("Location: messages.php");
    exit();
}

// ✅ Send reply
if (isset($_POST['send_reply'])) {
    $id    = (int)$_POST['message_id'];
    $reply = mysqli_real_escape_string($conn, trim($_POST['admin_reply']));
    if (!empty($reply)) {
        mysqli_query($conn,
            "UPDATE contact_messages 
             SET admin_reply='$reply', replied_at=NOW(), is_read=1 
             WHERE id=$id");
    }
    header("Location: messages.php");
    exit();
}

$messages = mysqli_query($conn,
    "SELECT * FROM contact_messages ORDER BY created_at DESC");
$unread = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM contact_messages WHERE is_read=0"))[0];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Messages - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .reply-form { display:none; margin-top:10px; }
        .reply-box  {
            width:100%; padding:8px; border:1.5px solid #ddd;
            border-radius:6px; font-size:13px; resize:none;
            height:70px; box-sizing:border-box; font-family:Arial,sans-serif;
        }
        .reply-box:focus { border-color:#2e7d32; outline:none; }
        .btn-reply  {
            background:#1565c0; color:white; border:none;
            padding:6px 14px; border-radius:4px; cursor:pointer;
            font-size:13px; margin-top:6px;
        }
        .btn-reply:hover { background:#0d47a1; }
        .replied-badge {
            background:#e8f5e9; color:#2e7d32;
            padding:3px 10px; border-radius:20px; font-size:12px;
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a href="dashboard.php" class="logo">🛒 FreshCart Admin</a>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="messages.php">
            Messages
            <?php if($unread > 0): ?>
                <span style="background:#c62828; color:white; border-radius:50%;
                             padding:1px 6px; font-size:11px; margin-left:4px;">
                    <?php echo $unread; ?>
                </span>
            <?php endif; ?>
        </a>
<a href="logout.php">Logout</a>
    </div>
</nav>

<div style="max-width:1100px; margin:40px auto; padding:0 20px;">
    <h2 style="color:#2e7d32; margin-bottom:20px;">
        📨 Contact Messages
        <?php if($unread > 0): ?>
            <span style="font-size:14px; color:#c62828; font-weight:normal;">
                (<?php echo $unread; ?> unread)
            </span>
        <?php endif; ?>
    </h2>

    <?php if (mysqli_num_rows($messages) === 0): ?>
        <p style="color:#777;">No messages yet.</p>
    <?php else: ?>
    <table class="admin-table">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message & Reply</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($m = mysqli_fetch_assoc($messages)): ?>
        <tr style="<?php echo $m['is_read'] == 0 ? 'background:#fff8e1;' : ''; ?>">
            <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
            <td><?php echo htmlspecialchars($m['email']); ?></td>
            <td><?php echo htmlspecialchars($m['subject']); ?></td>
            <td style="max-width:280px; font-size:13px;">
                <!-- User message -->
                <div style="color:#333; margin-bottom:6px;">
                    <?php
                    $msg = htmlspecialchars($m['message']);
                    echo strlen($msg) > 80 ? substr($msg, 0, 80) . '…' : $msg;
                    ?>
                </div>

                <!-- Show existing reply if any -->
                <?php if (!empty($m['admin_reply'])): ?>
                    <div style="background:#e8f5e9; border-left:3px solid #2e7d32;
                                padding:6px 10px; border-radius:4px; font-size:12px;
                                color:#1b5e20; margin-bottom:6px;">
                        <strong>✅ Your reply:</strong><br>
                        <?php echo htmlspecialchars($m['admin_reply']); ?>
                        <div style="color:#777; font-size:11px; margin-top:3px;">
                            <?php echo date('d M Y, h:i A', strtotime($m['replied_at'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reply form (toggle) -->
                <div class="reply-form" id="reply-<?php echo $m['id']; ?>">
                    <form method="POST">
                        <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                        <textarea name="admin_reply" class="reply-box"
                                  placeholder="Type your reply to <?php echo htmlspecialchars($m['name']); ?>..."
                                  required><?php echo htmlspecialchars($m['admin_reply'] ?? ''); ?></textarea>
                        <br>
                        <button type="submit" name="send_reply" class="btn-reply">
                            📤 Send Reply
                        </button>
                        <button type="button" onclick="toggleReply(<?php echo $m['id']; ?>)"
                                style="background:#777; color:white; border:none; padding:6px 12px;
                                       border-radius:4px; cursor:pointer; font-size:13px; margin-left:6px;">
                            Cancel
                        </button>
                    </form>
                </div>
            </td>

            <td><?php echo date('d M Y', strtotime($m['created_at'])); ?></td>

            <td>
                <?php if (!empty($m['admin_reply'])): ?>
                    <span class="replied-badge">Replied</span>
                <?php elseif ($m['is_read'] == 0): ?>
                    <span style="background:#fff3e0; color:#e65100;
                                 padding:3px 10px; border-radius:20px; font-size:12px;">
                        New
                    </span>
                <?php else: ?>
                    <span style="background:#f5f5f5; color:#777;
                                 padding:3px 10px; border-radius:20px; font-size:12px;">
                        Read
                    </span>
                <?php endif; ?>
            </td>

            <td style="white-space:nowrap;">
                <!-- Reply button -->
                <button onclick="toggleReply(<?php echo $m['id']; ?>)"
                        style="background:#2e7d32; color:white; border:none;
                               padding:5px 12px; border-radius:4px; cursor:pointer;
                               font-size:13px; margin-bottom:4px; display:block; width:100%;">
                    <?php echo !empty($m['admin_reply']) ? '✏️ Edit Reply' : '💬 Reply'; ?>
                </button>

                <?php if ($m['is_read'] == 0): ?>
                    <a href="messages.php?read=<?php echo (int)$m['id']; ?>"
                       style="color:#1565c0; font-size:12px; display:block; margin-bottom:4px;">
                        Mark Read
                    </a>
                <?php endif; ?>

                <a href="messages.php?delete=<?php echo (int)$m['id']; ?>"
                   style="color:#c62828; font-size:12px;"
                   onclick="return confirm('Delete this message?')">
                    🗑️ Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>

<footer class="footer"><p>© 2024 FreshCart | Admin Panel</p></footer>

<script>
function toggleReply(id) {
    const form = document.getElementById('reply-' + id);
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
    if(form.style.display === 'block') {
        form.querySelector('textarea').focus();
    }
}
</script>
</body>
</html>