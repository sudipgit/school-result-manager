<?php
function srm_result_shortcode() {
    ob_start(); ?>
    <form method="post">
        <input type="text" name="roll" placeholder="Roll">
        <input type="text" name="reg_no" placeholder="Reg No">
        <button type="submit">View Result</button>
    </form>
    <?php
    if ($_POST) {
        echo "<h3>Result for Roll: " . esc_html($_POST['roll']) . "</h3>";
        // Fetch and show marks here
    }
    return ob_get_clean();
}
add_shortcode('srm_result', 'srm_result_shortcode');
