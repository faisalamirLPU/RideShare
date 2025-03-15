document.getElementById("searchForm").addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent form submission

    let formData = new FormData(this);

    fetch("../ajax/search_rides.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById("searchResults").innerHTML = data;
    })
    .catch(error => console.error("Error:", error));
});
