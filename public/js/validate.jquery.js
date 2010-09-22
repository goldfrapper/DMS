//
// jQuery Form Validation
//

function validateForm(e)
{
	var form = e.currentTarget;
	
	// Required validation
	$('.required',form).each(function(){
		if($(this).val()=='') $(this).addClass('invalid');
		else $(this).removeClass('invalid');
	});
	
	// Email validation
	$('.email',form).each(function(){
		if(!$(this).val().match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i))
			$(this).addClass('invalid');
		else $(this).removeClass('invalid');
	});
	
	if($('.invalid',form).length) {
		e.preventDefault();
		return false;
	}
	else return true;
};

$(window).load(function(){
	$('form.validate').submit(validateForm);
});