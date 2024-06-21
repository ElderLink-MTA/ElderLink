const ratings = document.querySelectorAll(".rating");
const ratingsContainer = document.querySelector(".ratings-container");
const sendBtn = document.querySelector("#send");
const panel = document.querySelector("#panel");
let selectedRating = "Satisfied";

ratingsContainer.addEventListener("click", (e) => {
  removeActive();
  if (
    e.target.parentNode.classList.contains("rating") &&
    e.target.nextElementSibling
  ) {
    e.target.parentNode.classList.add("active");
    selectedRating = e.target.nextElementSibling.innerHTML;
  } else if (
    e.target.parentNode.classList.contains("rating") &&
    e.target.previousElementSibling
  ) {
    e.target.parentNode.classList.add("active");
    selectedRating = e.target.innerHTML;
  } else if (e.target.classList.contains("rating")) {
    e.target.classList.add("active");
    selectedRating = e.target.children[1].innerText;
  }
});

sendBtn.addEventListener("click", (e) => {
  panel.innerHTML = `
    <i class="fas fa-heart"></i>
    <strong>Thank You!</strong>
    <br> 
    <strong>Feedback: ${selectedRating}</strong>
    <p>We'll use your feedback to improve our customer support</p>
    `;
});

function removeActive() {
  ratings.forEach((rating) => rating.classList.remove("active"));
}

function showOtherOption(selectElement) {
  var otherOptionInput = document.getElementById('otherOption');
  if (selectElement.value === 'other') {
    otherOptionInput.classList.remove('d-none');
  } else {
    otherOptionInput.classList.add('d-none');
    otherOptionInput.value = ''; // Clear input value if user switches back from 'Other'
  }
}

document.getElementById("submit").addEventListener("click", function (event) {
  var experience = document.querySelector('input[name="experience"]:checked');
  var contribution = document.querySelector('input[name="contribution"]:checked');
  var sociability = document.querySelector('input[name="sociability"]:checked');

  if (!experience || !contribution || !sociability) {
    event.preventDefault(); // Prevent form submission
    document.getElementById("errorMessage").style.display = "block"; // Show error message
  }
});