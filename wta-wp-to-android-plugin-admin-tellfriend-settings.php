<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Tell some friends of us!!</h3>
        </div>
        <div class="panel-body">
			<p>
				Please keep inviting friends by sending you emails!
			</p>
			<label for="">Emails (separate by commas)</label>
			<input type="text" name="wta_email" id="wta_email" value="" class="form-control"/>
			<label for="">Message</label>
			<textarea id="wta_texto" name="wta_texto" class="form-control"></textarea>
			<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_nonce_field( 'tell_friend_' );?>" />
			<input type="hidden" name="wta_url" id="wta_url" value="<?php echo plugins_url('/wta_tell_friend.php', __FILE__);?>"/>
			<input type="button" id="wta_tellfriendbutton_submit" value="Submit" class="btn btn-primary"/>
        </div>
    </div>
</div>