document.getElementById("verifyPayment").addEventListener("click", function () {
    let bookingId = this.getAttribute("data-booking-id");

    fetch("../ajax/verify_payment.php", {
        method: "POST",
        body: new URLSearchParams({ booking_id: bookingId })
    })
    .then(response => response.json())
    .then(data => {
        let messageDiv = document.getElementById("paymentMessage");
        if (data.status === "success") {
            messageDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
        } else {
            messageDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
        }
    })
    .catch(error => console.error("Error:", error));
});
