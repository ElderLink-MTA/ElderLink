      function showOtherOption(selectElement) {
        const otherOptionInput = document.getElementById('otherOption');
        if (selectElement.value === 'other') {
          otherOptionInput.classList.remove('d-none');
          otherOptionInput.focus();
        } else {
          otherOptionInput.classList.add('d-none');
          otherOptionInput.value = '';
        }
      }

      function updateSelectValue() {
        const selectElement = document.getElementById('req_type');
        const otherOptionInput = document.getElementById('otherOption');
        if (selectElement.value === 'other' && otherOptionInput.value.trim() !== '') {
          selectElement.options[selectElement.selectedIndex].value = otherOptionInput.value.trim();
          selectElement.options[selectElement.selectedIndex].text = otherOptionInput.value.trim();
        }
      }

      // Initialize the existing rows
      document.querySelectorAll('.meeting-date').forEach(dateInput => {
        flatpickr(dateInput, {
          enableTime: false,
          dateFormat: "Y-m-d",
          minDate: "today"
        });
      });

      document.querySelectorAll('.meeting-start-time').forEach(timeInput => {
        flatpickr(timeInput, {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          minTime: "08:00",
          maxTime: "20:00",
          time_24hr: true
        });
      });

      document.querySelectorAll('.meeting-end-time').forEach(timeInput => {
        flatpickr(timeInput, {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          minTime: "08:00",
          maxTime: "20:00",
          time_24hr: true
        });
      });

      function addMeetingRow() {
        const container = document.getElementById('container');
        const row = document.createElement('div');
        row.classList.add('row', 'gx-3', 'mb-4');
        row.setAttribute('data-new', 'true');

        row.innerHTML = `
        <div class="col-md-4">
            <label class="small mb-1" for="meeting-date">תאריך</label>
            <input class="form-control meeting-date" name="new_dates[]" type="text" placeholder="הכנס תאריך" required>
        </div>
        <div class="col-md-3">
            <label class="small mb-1" for="meeting-start-time">שעת התחלה</label>
            <input class="form-control meeting-start-time" name="new_start_times[]" type="text" placeholder="הכנס שעת התחלה" required>
        </div>
        <div class="col-md-3">
            <label class="small mb-1" for="meeting-end-time">שעת סיום</label>
            <input class="form-control meeting-end-time" name="new_end_times[]" type="text" placeholder="הכנס שעת סיום" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-sm" style="margin-top:30px;" onclick="deleteRow(this)">מחק</button>
            <input type="hidden" name="req_times_ids[]" value="0">
            <input type="hidden" name="delete_times[]" value="0">
        </div>
    `;
        container.appendChild(row);

        flatpickr(row.querySelector('.meeting-date'), {
          enableTime: false,
          dateFormat: "d/m/Y",
          minDate: "today"
        });

        flatpickr(row.querySelector('.meeting-start-time'), {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          minTime: "08:00",
          maxTime: "20:00",
          time_24hr: true
        });

        flatpickr(row.querySelector('.meeting-end-time'), {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          minTime: "08:00",
          maxTime: "20:00",
          time_24hr: true
        });
      }

      document.getElementById('addRow').addEventListener('click', function (event) {
        event.preventDefault();
        addMeetingRow();
      });

      function deleteRow(button) {
        const row = button.closest('.row.gx-3.mb-4');
        if (row.getAttribute('data-new') === 'true') {
          row.remove(); // Remove the new row from the DOM
        } else {
          row.querySelector('input[name="delete_times[]"]').value = 1; // Mark the row for deletion
          row.style.display = 'none'; // Hide the row
        }
      }

      // Initialize the existing rows
      document.querySelectorAll('.meeting-date').forEach(dateInput => {
        flatpickr(dateInput, {
          enableTime: false,
          dateFormat: "d/m/Y",
          minDate: "today"
        });
      });

      document.querySelectorAll('.meeting-start-time').forEach(timeInput => {
        flatpickr(timeInput, {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          minTime: "08:00",
          maxTime: "20:00",
          time_24hr: true
        });
      });

      document.querySelectorAll('.meeting-end-time').forEach(timeInput => {
        flatpickr(timeInput, {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          minTime: "08:00",
          maxTime: "20:00",
          time_24hr: true
        });
      });