<?php
if (!current_user_can('edit_posts')) exit;


/* ---------- HANDLE FORM SUBMISSION ---------- */
if (isset($_POST['srm_save_academic_settings'])) {
    check_admin_referer('srm_academic_settings');

    srm_save_current_session($_POST);

    echo '<div class="notice notice-success"><p>Academic settings updated.</p></div>';
}

/* ---------- DATA ---------- */
$active_session = get_option('srm_active_session');
$active_exam    = intval(get_option('srm_active_exam'));
$exams          = srm_get_all_exams();
$sessions       = srm_get_sessions();
?>

<div class="wrap srm-dashboard">
<h1>Academic Settings</h1>

<form method="post">
    <?php wp_nonce_field('srm_academic_settings'); ?>

    <table class="form-table">
    <tr>
        <th>Active Session</th>
        <td>
            <select name="session" required>
                <option value="">Select Session</option>
                <?php foreach ($sessions as $s): ?>
                    <option value="<?= $s ?>" <?= $active_session==$s?'selected':'' ?>>
                        <?= $s ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <th>Active Exam</th>
        <td>
            <select name="exam_id" required>
                <option value="">Select Exam</option>
                <?php foreach ($exams as $e): ?>
                    <option value="<?= $e->id ?>" <?= $active_exam==$e->id?'selected':'' ?>>
                        <?= esc_html($e->name) ?> (<?= esc_html($e->session) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    </table>

    <p>
        <button class="button button-primary" name="srm_save_academic_settings">
            Save Settings
        </button>
    </p>
</form>
</div>
