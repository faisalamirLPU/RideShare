document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.getElementById("bookingForm");

    if (bookingForm) {
        bookingForm.addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent form submission

            let formData = new FormData(bookingForm);

            fetch("../ajax/book_ride.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let messageDiv = document.getElementById("bookingMessage");

                if (data.status === "success") {
                    messageDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
                    
                    // Update the available seats on the page
                    document.getElementById("availableSeats").innerText = data.remainingSeats;

                    // Disable the form if no more seats are available
                    if (data.remainingSeats <= 0) {
                        document.getElementById("seats").disabled = true;
                        bookingForm.querySelector("button").disabled = true;
                    }
                } else {
                    messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById("bookingMessage").innerHTML = `<p style="color: red;">Booking failed. Please try again.</p>`;
            });
        });
    }
});
