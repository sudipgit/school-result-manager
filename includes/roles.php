<?php
if (!defined('ABSPATH')) exit;

function srm_add_roles() {
    if (!get_role('teacher')) {
        add_role('teacher', 'Teacher', [
            'read' => true,
            'edit_posts' => false,
            'publish_posts' => false,
        ]);
    }
}

