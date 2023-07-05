<?php

//          Date      2014/10/18
//          FileName  index.php
//          Developer Hakuna Moni
	
?>

<html lang="en-US">
<head>

	<meta charset="utf-8">

	<title>Input LP Number</title>

	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Varela+Round">
	<link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/temando/css/style.css?v=43">
	<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/temando/js/jquery-1.8.3.min.js?v=43"></script>
</head>
<body>
	<div id="input_lp">		
		<h2><span class="logo"></span>Please input LP Number in here.</h2>
		<form method="post" action="" class="temando" id="temando">
			<fieldset>
				<p>
					<label class="lp-lable" for="lpnumber">LP # :</label>
					<input type="text" name="lp_number" id="lp_number" value="" onBlur="if(this.value=='')this.value='LP Number Here'" onFocus="if(this.value=='LP Number Here')this.value=''">
				</p>
				<p>
					<input type="button" onclick="runEngine();" id="submit_button" value="Enter">
				</p>
				<input type="hidden" name="location" id="location" value="<?php echo $location; ?>">
			</fieldset>

		</form>
		<div id="overlay">
			<div id="loading">
				<div class="bar1"></div>
				<div class="bar2"></div>
				<div class="bar3"></div>
				<div class="bar4"></div>
				<div class="bar5"></div>
				<div class="bar6"></div>
				<div class="bar7"></div>
				<div class="bar8"></div>
			</div>
		</div>
	</div> 
</body>
<script>
	jQuery('#lp_number').keypress( function(event){
		if ( event.which == 13 || event.keyCode == 13 ) {
			event.preventDefault();
			runEngine();
		}
	});
	function runEngine(){
		var lp_num = jQuery("#lp_number").val();
		lp_num = lp_num.replace(/ /g, '');
		if ( lp_num.length < 5 || lp_num.length > 15 || lp_num == 'LPNumberHere' )
		{
			alert('Please input valid LP number.');						
		}else{
			jQuery('#overlay').addClass('show');
			$.ajax({
				url: 'http://<?php echo $_SERVER['HTTP_HOST']; ?>/temando/temando.php',
				type: 'POST',
				data: jQuery('form#temando').serialize(),
				dataType: 'html',
				timeout: 90000000,
				error: function(){
					//alert('Error...please try again');
					jQuery('#overlay').removeClass('show');
					jQuery('#input_lp').append('<div id="alert_window"><div id="popup"></div><div id="confirm_btn" onclick="confirm(this);">Okay</div></div>');
					jQuery('#popup').html('Error...please try again');
				},
				success: function(html){
					jQuery('#overlay').removeClass('show');					
					if ( html.indexOf('DO NOT SHIP, RETURN TO MANAGER') != -1 )
					{
						jQuery('#input_lp').append('<div id="alert_window"><div id="popup"></div><div id="confirm_btn" onclick="confirm(this);">Okay</div></div>');
						jQuery('#popup').html(html);
						jQuery('#popup').addClass('bold');
					}else if ( html.indexOf('SUCCESS!') != -1 )
					{
						jQuery('#lp_number').val('');
						var labelFile = lp_num + '.pdf';
						window.open('http://shop247group.com/temando/label/' + labelFile, 'blank');
						jQuery('#lp_number').focus();
					}else{
						jQuery('#input_lp').append('<div id="alert_window"><div id="popup"></div><div id="confirm_btn" onclick="confirm(this);">Okay</div></div>');
						jQuery('#popup').html(html);
					}
					//alert(html);
				}
			});	
		}
	}
	function confirm(obj){
		jQuery(obj).parent().remove();
		jQuery('#lp_number').focus();
	}

	var count = 0;
	function rotate() {
		var elem2 = document.getElementById('loading');
		elem2.style.MozTransform = 'scale(0.5) rotate('+count+'deg)';
		elem2.style.WebkitTransform = 'scale(0.5) rotate('+count+'deg)';
		if (count==360) { count = 0 }
		count+=45;
		window.setTimeout(rotate, 100);
	}
	window.setTimeout(rotate, 100);
	jQuery( document ).ready(function() {
		jQuery('#lp_number').focus();
	});
</script>
</html>