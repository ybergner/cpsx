/* Javascript for CPSX. */
function cpsx(runtime, element) {

	$("#chatme1").submit(function(event) {
		// Stop the browser from submitting the form.
	event.preventDefault();
        	//events
        	var $formdata = $("#chatme1").serialize();
        	                alert("Could not proceed");
	});
}


function hide() {
        $(".cpsx_block").toggleClass("hide");
}
