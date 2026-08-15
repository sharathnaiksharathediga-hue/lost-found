const searchInput = document.getElementById("searchInput");
const typeFilter = document.getElementById("typeFilter");
const categoryFilter = document.getElementById("categoryFilter");

const cards = document.querySelectorAll(".item-card");


function filterItems() {

    const searchText =
        searchInput.value.toLowerCase();

    const selectedType =
        typeFilter.value;

    const selectedCategory =
        categoryFilter.value;


    cards.forEach(function(card) {

        const title =
            card.dataset.title;

        const type =
            card.dataset.type;

        const category =
            card.dataset.category;


        const matchesSearch =
            title.includes(searchText);

        const matchesType =
            selectedType === "All" ||
            type === selectedType;

        const matchesCategory =
            selectedCategory === "All" ||
            category === selectedCategory;


        if (
            matchesSearch &&
            matchesType &&
            matchesCategory
        ) {

            card.style.display = "block";

        } else {

            card.style.display = "none";

        }

    });

}


if (searchInput) {

    searchInput.addEventListener(
        "input",
        filterItems
    );

}


if (typeFilter) {

    typeFilter.addEventListener(
        "change",
        filterItems
    );

}


if (categoryFilter) {

    categoryFilter.addEventListener(
        "change",
        filterItems
    );

}