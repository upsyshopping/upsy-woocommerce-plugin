    function store_upsy_wc_auth_confirmation(){
            let $ = jQuery;
            $.get(upsy_wc_auth.ajax_url,{ action: "upsy_wc_auth_confirmation"});
    }


	function print_upsy_wooCommerce_authentication_success_message(){
		const successMessageSelector = document.getElementById("upsy_wc_auth_success_message");
   		const upsyAuthenticationBlock = document.getElementById("upsy_wc_auth_block");
		if (upsy_wc_auth.success === '1') {
			successMessageSelector.style.display = "block";
       		upsyAuthenticationBlock.style.display = "none";
          	store_upsy_wc_auth_confirmation();
           return true;
        } 
	}

	function print_upsy_wooCommerce_authentication_error_message(status=true) {
		const errorMessageSelector = document.getElementById("upsy_wc_auth_error_message");
		if (status) {
			errorMessageSelector.style.display = "block";
		} else {
			errorMessageSelector.style.display = "none";
		}
	}

	async function send_wc_authentication_request(){
		const storeUrl = upsy_wc_auth.host;
		const returnUrl=window.location.href;
		const baseUrl = upsy_wc_auth.environment === "production"
        ? "https://api.upsyshopping.com/v1/wc-auth"
        : "http://localhost:3000/dev/v1/wc-auth";
		
		try {
			const httpRequest = await fetch(`${baseUrl}?storeUrl=${encodeURI(storeUrl)}&returnUrl=${encodeURI(returnUrl)}`);
			const httpResult = await httpRequest.json();
			
			if (httpResult?.result) {
				window.location.replace(httpResult?.result);
			}
		} catch (error) {
			console.log(error);
			print_upsy_wooCommerce_authentication_error_message();
		}
	}

	function handle_upsy_wc_authentication() {
		const buttonSelector = document.getElementById('upsy_wc_auth_connection');
        if(!buttonSelector) {
           return false;
        }
		buttonSelector.addEventListener('click', function(){
				send_wc_authentication_request();
		})
	}


	function init() {
    	handle_upsy_wc_authentication();
		print_upsy_wooCommerce_authentication_success_message();
		print_upsy_wooCommerce_authentication_error_message(false);
	}

	window.addEventListener('load', init);
