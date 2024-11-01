<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <form method="post" action="options.php">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Application Menu Settings</h3>
            </div>
            <div class="panel-body">
                <?php
                    settings_fields("section-menues");
                    do_settings_sections("wp-android-menues");
                    submit_button();
                ?>
            </div>
        </div>
    </form>
</div>
