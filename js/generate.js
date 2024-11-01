jQuery( document ).ready(function() {
	jQuery("#generate_button_apk").bind( "click", function( event ) {

		jQuery("#progressbar").show();
		jQuery("#message_inprogress").show();

		var target = jQuery( event.target ),
        progressbar = jQuery( "#progressbar" ),
        progressbarValue = progressbar.find( ".ui-progressbar-value" );
 
      if ( target.is( "#numButton" ) ) {
        progressbar.progressbar( "option", {
          value: Math.floor( Math.random() * 100 )
        });
      } else if ( target.is( "#colorButton" ) ) {
        progressbarValue.css({
          "background": '#' + Math.floor( Math.random() * 16777215 ).toString( 16 )
        });
      } else if ( target.is( "#falseButton" ) ) {
        progressbar.progressbar( "option", "value", false );
      }

      var urlTarget = jQuery("#wta_wp_to_android_app_host_hidden").val();
	  	jQuery.post('http://free.wptoandroid.com/index.php', { url: urlTarget}, 
		    function(returnedData){
		       jQuery("#progressbar").hide();
		       jQuery("#message_inprogress").hide();
		       jQuery("#message_success").show();
		       if (returnedData == "success") {
		       	
		       } else {
		       		alert(returnedData);
		       }
		});
	});

	jQuery("#wta_tellfriendbutton_submit").bind( "click", function( event ) {
		var wta_text = jQuery("#wta_texto").val();
		var wta_email = jQuery("#wta_email").val();
		var wta_url = jQuery("#wta_url").val();

	  	jQuery.post(wta_url, { email: wta_email, texto : wta_text}, 
		   function(returnedData) {
		       jQuery("#wta_texto").val('');
		       jQuery("#wta_email").val('');
		});
	});

});