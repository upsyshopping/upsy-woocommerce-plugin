async function upsy_send_wc_authentication_request() {
  const storeUrl = upsy_wc_auth.host;
  const returnUrl = upsy_wc_auth.return_url;
  const baseUrl = upsy_wc_auth.environment === "production" ? "https://api.upsyshopping.com/v1/wc-auth" : "http://localhost:3000/dev/v1/wc-auth";

  try {
    const httpRequest = await fetch(
      `${baseUrl}?storeUrl=${encodeURI(storeUrl)}&returnUrl=${encodeURI(
        returnUrl
      )}`
    );
    const httpResult = await httpRequest.json();

    if (httpResult.result) {
      window.location.replace(httpResult.result);
    }
  } catch (error) {
    console.log(error);
  }
}


function upsy_handle_upsy_wc_authentication() {
  const buttonSelector = document.getElementById("upsy_wc_auth_connection");
  if (!buttonSelector) {
    return false;
  }
  buttonSelector.addEventListener("click", function () {
    upsy_send_wc_authentication_request();
  });
}

function upsy_init() {
  upsy_handle_upsy_wc_authentication();
}

window.addEventListener("load", upsy_init);
