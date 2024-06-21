function cancelRequest(reqId) {
if (confirm("האם את/ה בטוח/ה שברצונך לבטל את הבקשה?")) {
  console.log("Canceling request with ID: " + reqId); // Log the request ID
  // Send AJAX request
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "cancel_waiting_request.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      console.log("Response received: " + xhr.responseText); // Log the response
      if (xhr.status === 200) {
        // Handle response
        alert(xhr.responseText);
        window.location.reload();
        // Display response message
        // You can also update the UI accordingly if needed
      } else {
        alert("Error: " + xhr.statusText); // Display error message
      }
    }
  };
  xhr.send("req_id=" + reqId); // Send request ID as data
}
}