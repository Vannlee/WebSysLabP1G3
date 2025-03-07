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
            thumbnail.addEventListener("click", displayLargerPicture);
        }
    }
    else {
        console.log("No thumbnail images on webpage!");
    }
}

function displayLargerPicture(image) {
        var popupId = image.target.alt;
        var popup = document.getElementById(popupId);

        if (popup == null) {
            popup = document.createElement("span");
            popup.id = popupId;
            popup.setAttribute("class", "img-popup");
            popup.innerHTML = "<img src=\"images/" + popupId + "_large.jpg\">"
            image.target.insertAdjacentElement("afterend", popup);
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