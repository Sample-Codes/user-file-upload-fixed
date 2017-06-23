jQuery(document).ready(function($) {
    //repeatable fields
	if(jQuery.fn.repeatable_fields !== undefined) {
		jQuery('.ff-repeatable').each(function() {
			jQuery(this).repeatable_fields({
				wrapper: 'table',
				container: 'tbody',
				row: 'tr',
				add: '.ff-add-row',
				move: '.ff-move-row',
				template: '.ff-add-template',
			});
		});
	}
    
    //add media uploader
     var _custom_media = true,
      _orig_send_attachment = wp.media.editor.send.attachment;

  jQuery('.ff-repeatable').on('click', '._unique_name_button', function() {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    //var id = button.attr('id').replace('_button', '');
    _custom_media = true;
    wp.media.editor.send.attachment = function(props, attachment){
      if ( _custom_media ) {
        button.parent().parent().find(".file_url").val(attachment.url);
        button.parent().parent().find(".file_name").val(attachment.title);
        button.parent().parent().find(".file_description").val(attachment.description);
        button.parent().parent().find(".file_id").val(attachment.id);
        button.parent().parent().find(".file_oname").val(attachment.filename);
        button.parent().parent().find(".file_mime").val(attachment.mime);
      } else {
        return _orig_send_attachment.apply( this, [props, attachment] );
      };
    }

    wp.media.editor.open(button);
    return false;
  });

  $('.add_media').on('click', function(){
    _custom_media = false;
  });
  
  
     // remove row
    jQuery('.ff-repeatable').on('click', '.ff-remove-row', function() {
        file_id = jQuery(this).parent().find(".file_id").val();
        user_id = jQuery("#user_id").val();
        row = jQuery(this).parent().parent();
        if ( file_id ) {
          jQuery(this).parent().find("._unique_name_button").val('..Deleting File..');
          jQuery.ajax({
             type : "post",
             dataType : "json",
             url : ajaxurl,
             data : { action: "deletefile", file_id : file_id, user_id : user_id },
             success: function(response) {
                 if ( response.status == "sucess" ) {
                     row.remove();
                 } else {
                     row.find("._unique_name_button").val('..Unable to Delete..')
                 }
             }
            });
        } else {
            row.remove();
        }
    });
  
});