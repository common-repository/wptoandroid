<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <form method="post" enctype='multipart/form-data'>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Generate APK!</h3>
                </div>
            <div class="panel-body">
            	<img src="<?php echo plugins_url('/images/Android_robot.svg', __FILE__);?>" width="100" style="margin-right:5px;"/>
				<input type="button" id="generate_button_apk" value="Generate" class="btn btn-primary"/>
            </div>
        </div>
        <p>You will be receiving in some minutes an unsigned APK you the email that you have registered in the setup of the plugin. If you have any issues please email us to <a href="mailto:martin@infuy.com">Email</a> in order to assist you.</p>
        <p><span style="color:red;">IMPORTANT</span> You web should be published on Internet, example we can not generate something which is pointing to your local machine! Thanks !</p> 
    </form>

    <script>
	    jQuery( function() {
	    	jQuery( "#progressbar" ).progressbar({
		      value: false
		    });
	    });
    </script>

    <input type="hidden" id="wta_wp_to_android_app_host_hidden" name="wta_wp_to_android_app_host_hidden" value="<?php echo get_option('wta_wp_to_android_app_host');?>"/>
    <div id="progressbar" style="display:none;"></div>
    <div id="message_inprogress" style="display:none;font-weight:bold;color:#337ab7;">In progress! Please wait, this could take some minutes... Thanks for your patience!</div>
    <div id="message_success" style="display:none;font-weight:bold;color:red;">Task Completed! Please check your email and enjoy!</div>
</div>