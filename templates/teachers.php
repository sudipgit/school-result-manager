<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;
$users_table   = $wpdb->prefix . 'users';
$assign_table  = $wpdb->prefix . 'srm_subject_assignments';
$subjects_table= $wpdb->prefix . 'srm_subjects';
$classes_table = $wpdb->prefix . 'srm_classes';
$sections_table= $wpdb->prefix . 'srm_sections';

// Check if we are viewing a teacher profile
$view_teacher_id = isset($_GET['view']) ? intval($_GET['view']) : 0;

if ($view_teacher_id) {

    $teacher = get_userdata($view_teacher_id);
    if (!$teacher) {
        echo '<div class="notice notice-error"><p>Teacher not found.</p></div>';
        return;
    }

    // Fetch assigned subjects
    $assignments = $wpdb->get_results($wpdb->prepare(
        "SELECT a.*, s.name as subject_name, c.name as class_name, sec.name as section_name
         FROM $assign_table a
         LEFT JOIN $subjects_table s ON a.subject_id = s.id
         LEFT JOIN $classes_table c ON a.class_no = c.class_no
         LEFT JOIN $sections_table sec ON a.section_id = sec.id
         WHERE a.teacher_id = %d
         ORDER BY c.class_no ASC, sec.name ASC, s.name ASC",
         $view_teacher_id
    ));
    ?>

    <div class="wrap">
        <h1>Teacher Profile: <?= esc_html($teacher->display_name) ?></h1>
        <p><strong>Username:</strong> <?= esc_html($teacher->user_login) ?></p>
        <p><strong>Email:</strong> <?= esc_html($teacher->user_email) ?></p>
        <p><a href="<?= admin_url('admin.php?page=srm_teachers') ?>" class="button">Back to Teachers List</a></p>

        <h2>Assigned Subjects</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($assignments): foreach ($assignments as $a): ?>
                    <tr>
                        <td><?= esc_html($a->class_name) ?></td>
                        <td><?= esc_html($a->section_name) ?></td>
                        <td><?= esc_html($a->subject_name) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center;">No subjects assigned</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php
    return; // stop further execution so the list does not show
}

// Fetch all teachers
$teachers = $wpdb->get_results("
    SELECT u.ID, u.user_login, u.user_email, u.display_name
    FROM $users_table u
    INNER JOIN {$wpdb->prefix}usermeta um
        ON u.ID = um.user_id
    WHERE um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%teacher%'
    ORDER BY u.ID DESC
");
?>

<div class="wrap">
    <h1>Teachers
        <a href="<?= admin_url('user-new.php?role=teacher') ?>" class="page-title-action">Add Teacher</a>
    </h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Display Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($teachers): foreach ($teachers as $t): ?>
                <tr>
                    <td><?= intval($t->ID) ?></td>
                    <td><?= esc_html($t->user_login) ?></td>
                    <td><?= esc_html($t->display_name) ?></td>
                    <td><?= esc_html($t->user_email) ?></td>
                    <td>
                        <a href="<?= admin_url('admin.php?page=srm_teachers&view=' . $t->ID) ?>">View</a> |
                        <a href="<?= admin_url('user-edit.php?user_id=' . $t->ID) ?>">Edit</a> |
                        <a href="<?= wp_nonce_url(admin_url('users.php?action=delete&user=' . $t->ID), 'bulk-users') ?>" onclick="return confirm('Delete this teacher?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5" style="text-align:center;">No teachers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
