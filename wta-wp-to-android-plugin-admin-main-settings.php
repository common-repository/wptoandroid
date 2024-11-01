<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <div class="row">
        <div class="col-md-7">
            <form method="post" action="options.php">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Main Settings</h3>
                    </div>
                    <div class="panel-body">
                        <?php
                            settings_fields("section");
                            do_settings_sections("wp-android-options");
                            submit_button();
                        ?>
                    </div>
                </div>
            </form>
            <form method="post" action="options.php" >
                <div class="panel panel-primary">
                    <div class="panel-heading">
                      <h3 class="panel-title">Color Settings</h3>
                    </div>
                    <div class="panel-body">
                        <?php
                            settings_fields("section-colors");
                            do_settings_sections("wp-android-colors-options");
                            submit_button();
                        ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-5 text-center">
            <div class="marvel-device nexus5">
                <div class="top-bar"></div>
                <div class="sleep"></div>
                <div class="volume"></div>
                <div class="camera"></div>
                <div class="screen">
                     <iframe width="100%" height="100%" src="<?php echo plugins_url('/mobile.html', __FILE__);?>" id="fullpreviews"></iframe> 
                </div>
            </div>
        </div>
    </div>

</div>
