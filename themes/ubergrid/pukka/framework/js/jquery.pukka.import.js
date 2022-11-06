"use strict";

jQuery(document).ready(function($){
	var importing = false;
	var errorCount = 0;
	var maxRetrys = 20;
	
	$('#pukka-import').click(function(e){
		if(importing) return;
		if(!confirm('Are you sure you want to import demo content?')) return;
		importing = true;
		var html = $('#pukka-import-frame').html('');
		$('#waiting-import').css('display', 'inline');
		$('#pukka-import-frame').css('display', 'block');
		importData();
	});


	function importData(){
		var html = '<h2>Data import started.</h2><p>Please be patient, this can take a couple of minutes...</p>';
		$('#pukka-import-frame').removeClass('error').html(html);
		var data = {
			action: 'pukka_start_import'
		};
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				var html = $('#pukka-import-frame').html();
				var data = {};
				try{
					data = JSON.parse(response);
				}catch(error){
					html = '<h2>An Error Occured!</h2><p>There was an error while importing data.</p>';
					$('#pukka-import-frame').addClass('error').html(html);
					errorCount = 0;
					data.error = true;
					setupOptions();
				}
				if(false == data.error){
					$('#pukka-import-frame').html(html + "<p>Data imported successfully.</p>");
					errorCount = 0;
					setupOptions();
				}else{
					html += '<h2>An Error Occured!</h2><p>There was an error while importing data. Please try again.</p>';
					$('#pukka-import-frame').addClass('error').html(html);
					errorCount = 0;
					importing = false;
				}
			},
			error: function(jqXHR, error, exception){
				var html = $('#pukka-import-frame').html();
				html += "<p>An error occurred!<br/>Error type: " + error + "<p>";				
				errorCount++;
				if(errorCount < maxRetrys){
					html += '<p>Retrying...</p>';
					$('#pukka-import-frame').html(html);
					importData();
				}else{
					html += '<h2>An Error Occured!</h2><p>Too many server errors. Aborting import...</p>';
					$('#pukka-import-frame').addClass('error').html(html);
					$('#waiting-import').css('display', 'none');
					importing = false;
				}
			}
		});
	}

	function setupOptions(){
		var html = $('#pukka-import-frame').html();
		$('#pukka-import-frame').html(html + "<p>Setting theme options...</p>");
		var data = {
			action: 'pukka_setup_theme_options'
		};
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				var html = $('#pukka-import-frame').html();
				var data = {};
				try{
					data = JSON.parse(response);
				}catch(error){
					data.error = true;
				}
				if(false == data.error){
					setThemeOptions(data.options);
					$('#pukka-import-frame').html(html + "<p>All done! You can check out results <a id='homepage-url' href='" + data.url + "' target='_blank'><b>here</b></a>.</p>");
					setTimeout(function(e){
						window.open(data.url, '_blank');
					}, 3000);					
				}else{
					$('#pukka-import-frame').addClass('error').html(html + "<p><b>Data import failed.</b><p>");
				}
				$('#waiting-import').css('display', 'none');
				importing = false;
			},
			error: function(jqXHR, error, exception){
				var html = $('#pukka-import-frame').html();
				html += "<p>An error occurred!<br/>Error type: " + error + "<p>";
				errorCount++;
				if(errorCount < maxRetrys){
					html += '<p>Retrying...</p>';
					$('#pukka-import-frame').html(html);
					setupOptions();
				}else {					
					html += '<h2>An Error Occured!</h2><p>Too many server errors. Aborting import...</p>';
					$('#pukka-import-frame').html(html);
					$('#waiting-import').css('display', 'none');
					importing = false;
				}
			}
		});	
	}
});