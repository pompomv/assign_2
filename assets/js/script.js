document.addEventListener("DOMContentLoaded", function () {
    let navbar = document.querySelector(".navbar");
    let lastScrollTop = 0;
    let isScrollingDown = false;

    window.addEventListener("scroll", function () {
        let scrollTop = window.scrollY;

        if (scrollTop > lastScrollTop) {
            if (!isScrollingDown) {
                navbar.classList.add("hide-navbar");
                isScrollingDown = true;
            }
        } else {
            if (isScrollingDown) {
                navbar.classList.remove("hide-navbar");
                isScrollingDown = false;
            }
        }

        lastScrollTop = scrollTop;
    });
});

document.addEventListener("DOMContentLoaded", function () {
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let dropdown = document.querySelector("#kursusDropdown");

    dropdown.addEventListener("click", function () {
        this.parentElement.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
        if (!dropdown.parentElement.contains(event.target)) {
            dropdown.parentElement.classList.remove("show");
        }
    });
});
