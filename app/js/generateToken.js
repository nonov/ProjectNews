function generateToken(){
	var req = new XMLHttpRequest();
	req.open("GET", "/project/RESTapi/public/user/token", true);
	req.send();

	req.onreadystatechange = function() {
	    if (req.readyState === 4) { 
	        if (req.status === 200) { 
	            console.log("Success generateToken() : "+req.responseText);
	        } else { 
	        	console.log("Error generateToken()");
	        }
	    }
	};
};

generateToken();