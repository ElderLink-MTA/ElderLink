document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('tech-help').addEventListener('click', function () {
    window.location.href = 'new_request.php?req_type=עזרה טכנולוגית';
  });

  document.getElementById('shopping-help').addEventListener('click', function () {
    window.location.href = 'new_request.php?req_type=סיוע בקניות';
  });

  document.getElementById('leisure-together').addEventListener('click', function () {
    window.location.href = 'new_request.php?req_type=פנאי יחדיו';
  });

  document.getElementById('medicine-purchase').addEventListener('click', function () {
    window.location.href = 'new_request.php?req_type=רכישת תרופות';
  });

  document.getElementById('doctor-escort').addEventListener('click', function () {
    window.location.href = 'new_request.php?req_type=ליווי לרופא';
  });
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