                // JavaScript function to handle expanding div
                function toggleExpand(event) {
                    event.preventDefault();
                    const listItem = this.closest('.list-group-item');
                    listItem.classList.toggle('expanded');
                }

                // Add event listener to the entire list-group-item box
                document.querySelectorAll('.list-group-item').forEach(box => {
                    box.addEventListener('click', toggleExpand);
                });

                // JavaScript function to handle expanding div
                function toggleExpand(event) {
                    event.preventDefault();
                    const listItem = this.closest('.list-group-item');
                    listItem.classList.toggle('expanded');
                }

                // Add event listener to the entire list-group-item box
                document.querySelectorAll('.list-group-item').forEach(box => {
                    box.addEventListener('click', toggleExpand);
                });
                /* global bootstrap: false */
                (() => {
                    'use strict'
                    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.forEach(tooltipTriggerEl => {
                        new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                })()


                    (() => {
                        'use strict'

                        document.querySelector('#navbarSideCollapse').addEventListener('click', () => {
                            document.querySelector('.offcanvas-collapse').classList.toggle('open')
                        })
                    })()

                function showConfirmationPopup(reqId) {
                    // Display a confirmation dialog
                    var confirmation = confirm("האם את/ה בטוח/ה שאת/ה רוצה לאשר את הבקשה?");
                    if (confirmation) {
                        // Get the selected time slot from the dropdown
                        var selectedTimeSlot = document.getElementById('time-slots-' + reqId).value;

                        // Send an AJAX request to the server
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "update_request.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                // Handle the response from the server
                                var response = xhr.responseText;
                                alert(response);
                                // Check if the response indicates success
                                if (response.trim() === "הבקשה שובצה בהצלחה!") {
                                    // Redirect the user to placed_requests.php
                                    window.location.href = "placed_requests.php";
                                }
                            }
                        };
                        // Prepare the data to be sent
                        var data = "req_id=" + reqId + "&selected_time_slot=" + encodeURIComponent(selectedTimeSlot);
                        // Send the request
                        xhr.send(data);
                    }
                }


                function handleTimeSlotClick(event) {
                    event.stopPropagation(); // Prevent event from bubbling up
                }



                function showContent(id) {
                    // Hide all sections
                    document.querySelectorAll('.responsive-div').forEach(function (el) {
                        el.style.display = 'none';
                    });

                    // Show the selected section
                    document.getElementById(id).style.display = 'block';
                }
