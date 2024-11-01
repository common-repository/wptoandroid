<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <form method="post" action="options.php" enctype='multipart/form-data'>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Application Splash Settings</h3>
            </div>
            <div class="panel-body splash--settings">
                <?php
                    settings_fields("section-splash");
                    do_settings_sections("wp-android-splash-options");
                    submit_button();
                ?>
            </div>
        </div>
    </form>
    <a href="admin.php?page=wp_to_android_plugin_headernav_settings" class="btn btn-primary pull-right">Next</a>
</div>
