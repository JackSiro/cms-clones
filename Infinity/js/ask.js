jQuery(document).ready(function(){
	if($( "#category_tag_holder" ).length){
		var categorylist = $('#category_tag_holder').magicSuggest({
			data: it_ajax_category_url,
			//expandOnFocus: true,
			//hideTrigger: true,
			minChars: 0,
			placeholder: 'Type or select a catgory',
			noSuggestionText: 'Create this new category',
			maxEntryLength: 40,
			maxSelection: 1,
			useTabKey: false,
			highlight: false,
			renderer: function(data){
				meta = jQuery.parseJSON(data.meta);
				if ( meta !== null ){
					var output = '<div class="sl-category-item">';
					if ( meta.tips_cat_icon48 !==null && meta.tips_cat_icon48 !==undefined )
						output += '<img src="' + meta.tips_cat_icon48 + '" />';
					else if ( it_new_category_icon.trim() )
						output += '<img src="' + it_new_category_icon + '" />';
					if ( meta.tips_cat_desc !== null && meta.tips_cat_desc !==undefined)
						output += '<div class="sl-category-title">' + data.title + '</div>';
					else if ( data.title.trim() )
						output += '<div class="sl-category-title alone">' + data.title + '</div>';
					output += '<div class="sl-category-description">';
					if ( meta.tips_cat_desc !== null &&  meta.tips_cat_desc)
						output += '<div class="sl-category-detail">' + meta.tips_cat_desc + '</div>';
					output += '<div class="sl-category-qcount">' + data.qcount + ' Tips</div>';
					output += '</div>';
					output += '</div>';
				}else{
					var output = '<div class="sl-category-item">';
					output += '<img src="' + it_new_category_icon + '" />';
					output += '<div class="sl-category-title alone">' + data.title + '</div>';
					output += '<div class="sl-category-description">';
					output += '<div class="sl-category-qcount alone">' + data.qcount + ' Tips</div>';
					output += '</div>';
					output += '</div>';
				}
				return output;
			},
			/*
			selectionRenderer: function(data){
				var output = '';
				meta = jQuery.parseJSON(data.meta);
				if ( meta !== null ){
					output = '<img src="' + meta.tips_cat_icon48 + '" />';
					output += '<div class="name">' + data.title + '</div>';
					return output;
				}
				return data.title;
			}
			*/
		});
		$(categorylist).on('keyup', function(e,m){
			item = this.getRawValue();
			$('#category_tag').val(item);
			//$('#category_tag').val(item[0].name);
		});
		
		// hide category field here
		$('#category_tag').hide();
		// set value of selected category to list
		if($('#category_tag').val().length !== 0){
			var item = [{ "id": $('#category_tag').val(), "name": $('#category_tag').val()}];
			categorylist.setValue(item);
		}
	}
	// Upload Featured file
	$("#featured_file_upload").uploadFile({
		url:it_ajax_featured_upload_url,
		allowedTypes:"png,gif,jpg,jpeg",
		fileName:"myfile",
		maxFileSize:1024*1000,
		maxFileCount:1,
		dragDropStr: "",
		showProgress:false,
		showStatusAfterSuccess:false,
		showFileCounter :false,
		showAbort:false,
		showDone:false,
		onSuccess:function(files,data,xhr)
		{
			var u_files = $.parseJSON( data );
			$("#featured_image").val(u_files[0]);
			$("#featured_file_container").hide();
			$("#image-preview").attr("src",it_featured_url_abs + 'featured/' + u_files[0]);
			$("#image-preview-container").show(500);
		},
		onError: function(files,status,errMsg)
		{
			alert("Error for: "+JSON.stringify(files));
		},
	});
	$("#remove-featured-image").click(function () { 
		$('#image-preview-container').hide(250);
		$("#featured_file_container").show(500);
		/*
		// now it uses event to remove file
		$.ajax({
			url: it_ajax_featured_delete_url,
			context: $("#featured_image").val(),
			type: 'post',
			
			data: {name: $("#featured_image").val()},
			success: function(output) {
				$("#featured_image").val('');
			}
			
		});
		*/
		$("#featured_image").val('');
	});

});