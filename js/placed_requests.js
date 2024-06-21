function confirmCancel(requestId) {
        if (confirm("האם את/ה בטוח/ה שברצונך לבטל את הבקשה?")) {
          // Send Ajax request to cancel the request
          var xhr = new XMLHttpRequest();
          xhr.open("POST", "cancel_placed_request.php", true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
              if (xhr.status === 200) {
                // Request successfully canceled, you can reload the page or update UI as needed
                alert(xhr.responseText);
                window.location.reload(); // Reload the page for demonstration, you can use another method if needed
              } else {
                // Handle error
                console.error("Error occurred while canceling the request.");
              }
            }
          };
          xhr.send("request_id=" + requestId);
        }
      }