document.addEventListener("DOMContentLoaded", function () {
    // Code to be executed when the DOM is ready (i.e. the document is fully loaded):
    registerEventListeners();
    activateMenu();
});

function registerEventListeners() {
    var thumbnails = document.getElementsByClassName("img-thumbnail");
    if (thumbnails !== null && thumbnails.length > 0) {
        for (var index = 0; index < thumbnails.length; index++) {
            var thumbnail = thumbnails[index];
            thumbnail.addEventListener("click", displayModal);
            //     function() {
            // });
        }
    }
    else {
        console.log("No thumbnail images on webpage!");
    }
}

function displayModal(image) {
    var image_path = image.target.src;
    var popupId = image.target.alt;
    var popup = document.getElementById(popupId);
    var location = image.target.getAttribute('location');
    var hours = image.target.getAttribute('hours');
    var contact = image.target.getAttribute('contact');

    // If modal exists, remove it
    if (popup == null) {
        // Create modal container
        popup = document.createElement("div");
        popup.id = popupId;
        popup.setAttribute("class", "modal");

        // Create modal content
        var modalContent = document.createElement("div");
        modalContent.setAttribute("class", "modal-content");

        // Create close button
        var closeButton = document.createElement("span");
        closeButton.setAttribute("class", "close-button");
        closeButton.innerHTML = "&times;";
        closeButton.addEventListener("click", function() {
            popup.remove();
        });

        // Create image element
        var imgElement = document.createElement("img");
        imgElement.src = image_path;
        imgElement.setAttribute("class", "modal-image");

        // Create text container for gym information
        var infoContainer = document.createElement("div");
        infoContainer.setAttribute("class", "modal-text");
        infoContainer.innerHTML = `
        <h2>${popupId}</h2>
        <p>Location: ${location}<br>
        Hours: ${hours}<br>
        Contact Number: ${contact}</p>`;

        // Create button to redirect to booking page
        var bookingButton = document.createElement("button");
        bookingButton.textContent = "Book Now";
        bookingButton.setAttribute("class", "btn btn-success");
        bookingButton.addEventListener("click", function() {
            window.location.href = "booking.php";
        })

        // Append elements
        infoContainer.appendChild(bookingButton);
        modalContent.appendChild(closeButton);
        modalContent.appendChild(imgElement);
        modalContent.appendChild(infoContainer);
        popup.appendChild(modalContent);
        document.body.appendChild(popup);
    }
    else {
        popup.remove();
    }
}

/*
* This function sets the currently selected menu item to the 'active' state.
* It should be called whenever the page first loads.
*/
function activateMenu() {
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        if (link.href === location.href) {
            link.classList.add('active');
        }
    })
}