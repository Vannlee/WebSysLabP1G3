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
    var location = image.target.getAttribute('data-location');
    var booking_slots = image.target.getAttribute('data-booking_slots');
    var contact = image.target.getAttribute('data-contact');
    
    // If modal exists, remove it
    if (popup == null) {
        // Create modal container
        popup = document.createElement("div");
        popup.id = popupId;
        popup.setAttribute("class", "modal");
        
        // Add accessibility attributes for dialog
        popup.setAttribute("role", "dialog");
        popup.setAttribute("aria-labelledby", "modal-title");
        popup.setAttribute("aria-modal", "true");

        // Create modal content
        var modalContent = document.createElement("div");
        modalContent.setAttribute("class", "modal-content");
        modalContent.setAttribute("role", "document");

        // Create close button
        var closeButton = document.createElement("span");
        closeButton.setAttribute("class", "close-button");
        closeButton.innerHTML = "&times;";
        closeButton.setAttribute("tabindex", "0");
        closeButton.setAttribute("aria-label", "Close dialog");
        closeButton.addEventListener("click", function() {
            popup.remove();
        });

        // Create title element that matches the aria-labelledby
        var modalTitle = document.createElement("h2");
        modalTitle.id = "modal-title";
        modalTitle.textContent = popupId;
        modalTitle.setAttribute("class", "modal-title");

        // Create image element
        var imgElement = document.createElement("img");
        imgElement.src = image_path;
        imgElement.setAttribute("class", "modal-image");
        imgElement.setAttribute("alt", popupId + " gym location");

        // Create text container for gym information
        var infoContainer = document.createElement("div");
        infoContainer.setAttribute("class", "modal-text");
        infoContainer.innerHTML = `
        <p>Location: ${location}<br>
        Booking Slots: ${booking_slots}<br>
        Contact Number: ${contact}</p>`;

        // Create button to redirect to booking page
        var bookingButton = document.createElement("button");
        bookingButton.textContent = "Book Now";
        bookingButton.setAttribute("class", "btn btn-success");
        bookingButton.setAttribute("aria-label", "Book an appointment at " + popupId);
        bookingButton.addEventListener("click", function() {
            window.location.href = "timetable.php";
        })

        // Append elements
        infoContainer.appendChild(bookingButton);
        modalContent.appendChild(closeButton);
        modalContent.appendChild(modalTitle);
        modalContent.appendChild(imgElement);
        modalContent.appendChild(infoContainer);
        popup.appendChild(modalContent);
        document.body.appendChild(popup);

        // Add event listener for ESC key to close modal
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape" && document.getElementById(popupId)) {
                popup.remove();
            }
        });
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