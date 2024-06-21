document.getElementById("deleteAccountBtn").addEventListener("click", function() {
            if (confirm("האם אתה בטוח שברצונך למחוק את החשבון שלך?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_account.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Redirect or show a message after successful deletion
                        alert(xhr.responseText); // For testing purposes, you can alert the response
                        // Redirect the user after deletion
                        window.location.href = "../../index.html";
                    }
                };
                xhr.send();
            }
        });

document.getElementById('passwordForm').addEventListener('submit', function(event) {
    event.preventDefault();

    // Clear previous error messages
    document.getElementById('currentPasswordError').textContent = '';
    document.getElementById('newPasswordError').textContent = '';
    document.getElementById('confirmPasswordError').textContent = '';
    document.getElementById('successMessage').textContent = '';

    var currentPassword = document.getElementById('currentPassword').value;
    var newPassword = document.getElementById('newPassword').value;
    var confirmPassword = document.getElementById('confirmPassword').value;

    if (!currentPassword) {
        document.getElementById('currentPasswordError').textContent = 'שדה זה חובה.';
        return;
    }
    if (!newPassword) {
        document.getElementById('newPasswordError').textContent = 'שדה זה חובה.';
        return;
    }
    if (!confirmPassword) {
        document.getElementById('confirmPasswordError').textContent = 'שדה זה חובה.';
        return;
    }
    
    // Check if new password is the same as the current password
    if (newPassword === currentPassword) {
        document.getElementById('newPasswordError').textContent = 'סיסמה חדשה חייבת להיות שונה מהסיסמה הנוכחית.';
        return;
    }

    // Validate new password pattern
    var pattern = /^(?=.*[a-zA-Z])(?=.*[0-9]).{6,}$/;
    if (!pattern.test(newPassword)) {
        document.getElementById('newPasswordError').textContent = 'סיסמה חדשה חייבת להכיל לפחות 6 תווים ולכלול לפחות מספר אחד ולפחות אות אחת באנגלית.';
        return;
    }

    if (!pattern.test(confirmPassword)) {
        document.getElementById('confirmPasswordError').textContent = 'סיסמה חדשה חייבת להכיל לפחות 6 תווים ולכלול לפחות מספר אחד ולפחות אות אחת באנגלית.';
        return;
    }

    var formData = new FormData(this);

    fetch('update_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            if (data.message.includes('נוכחית')) {
                document.getElementById('currentPasswordError').textContent = data.message;
            } else if (data.message.includes('אינן תואמות') || data.message.includes('חדשה')) {
                document.getElementById('newPasswordError').textContent = data.message;
                document.getElementById('confirmPasswordError').textContent = data.message;
            } else {
                document.getElementById('newPasswordError').textContent = data.message;
            }
        } else if (data.status === 'success') {
            document.getElementById('successMessage').textContent = data.message;
        }
    })
    .catch(error => console.error('Error:', error));
});
