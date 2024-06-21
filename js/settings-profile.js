// Change the id in the jQuery click event listener to match the button's name
$(document).ready(function () {
    // Add click event listener to the save button
    $("button[name='saveButton']").click(function () {
        // Serialize form data
        var formData = $("#profileForm").serialize();

        // Send form data to the PHP script asynchronously
        $.ajax({
            type: "POST",
            url: "settings-profile.php",
            data: formData,
            success: function (response) {
                // Handle success response
                alert(response); // Display success message or handle as needed
            },
            error: function (xhr, status, error) {
                // Handle error
                alert("An error occurred: " + xhr.responseText); // Display error message or handle as needed
            }
        });
    });
});

// Get the input element
        var dateOfBirthInput = document.getElementById('dateOfBirth');
    
        // Add an event listener to listen for changes in the input
        dateOfBirthInput.addEventListener('change', function() {
            // Get the selected date
            var selectedDate = new Date(this.value);
            
            // Calculate the age
            var today = new Date();
            var age = today.getFullYear() - selectedDate.getFullYear();
            var monthDiff = today.getMonth() - selectedDate.getMonth();
            
            // If the birth date has not occurred yet this year, subtract one year
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < selectedDate.getDate())) {
                age--;
            }
    
            // Check if the age is less than 16
            if (age < 16) {
                alert("הגיל המינימלי לרישום הינו 16.");
                // Clear the input value
                dateOfBirthInput.value = "";
            }
        });
    
            document.getElementById('validationTooltiPhone').addEventListener('input', function (e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');

            // Attach event listeners to each checkbox
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    // Display the save message
                    document.getElementById('saveMessage').style.display = 'block';

                    // Hide the message after 3 seconds
                    setTimeout(function () {
                        document.getElementById('saveMessage').style.display = 'none';
                    }, 3000);
                });
            });

            // Select the select element
            const selectElement = document.getElementById('field2');

            // Attach an event listener to the select element
            selectElement.addEventListener('change', function () {
                // Display the save message
                document.getElementById('saveMessage').style.display = 'block';

                // Hide the message after 3 seconds
                setTimeout(function () {
                    document.getElementById('saveMessage').style.display = 'none';
                }, 3000);
            });

        $(document).ready(function () {
            // Add click event listener to the save button
            $("#saveButton").click(function () {
                // Serialize form data
                var formData = $("#profileForm").serialize();

                // Send form data to the PHP script asynchronously
                $.ajax({
                    type: "POST",
                    url: "settings_profile.php",
                    data: formData,
                    success: function (response) {
                        // Handle success response
                        alert(response); // Display success message or handle as needed
                    },
                    error: function (xhr, status, error) {
                        // Handle error
                        alert("An error occurred: " + xhr.responseText); // Display error message or handle as needed
                    }
                });
            });
        });
