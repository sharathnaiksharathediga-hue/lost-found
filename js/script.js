/**
 * Lost & Found Application Client Script
 * Handles real-time search, multi-criteria filtering, live image previews, and UI enhancements.
 */

document.addEventListener("DOMContentLoaded", function () {
    // ---------------------------------------------------------
    // 1. Search & Multi-Filter for Browse Items
    // ---------------------------------------------------------
    const searchInput = document.getElementById("searchInput");
    const typeFilter = document.getElementById("typeFilter");
    const categoryFilter = document.getElementById("categoryFilter");
    const statusFilter = document.getElementById("statusFilter");
    const itemsGrid = document.getElementById("itemsGrid");
    const cards = document.querySelectorAll(".item-card");
    const emptyState = document.getElementById("noResultsState");
    const resultsCountEl = document.getElementById("resultsCount");

    function filterItems() {
        if (!cards.length) return;

        const searchText = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const selectedType = typeFilter ? typeFilter.value : "All";
        const selectedCategory = categoryFilter ? categoryFilter.value : "All";
        const selectedStatus = statusFilter ? statusFilter.value : "All";

        let visibleCount = 0;

        cards.forEach(function (card) {
            const title = (card.dataset.title || "").toLowerCase();
            const location = (card.dataset.location || "").toLowerCase();
            const desc = (card.dataset.description || "").toLowerCase();
            const type = card.dataset.type || "";
            const category = card.dataset.category || "";
            const status = card.dataset.status || "Active";

            const matchesSearch = searchText === "" ||
                title.includes(searchText) ||
                location.includes(searchText) ||
                desc.includes(searchText) ||
                category.toLowerCase().includes(searchText);

            const matchesType = selectedType === "All" || type === selectedType;
            const matchesCategory = selectedCategory === "All" || category === selectedCategory;
            const matchesStatus = selectedStatus === "All" || status === selectedStatus;

            if (matchesSearch && matchesType && matchesCategory && matchesStatus) {
                card.style.display = "flex";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        if (resultsCountEl) {
            resultsCountEl.textContent = visibleCount;
        }

        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? "block" : "none";
        }
    }

    if (searchInput) {
        searchInput.addEventListener("input", filterItems);
    }
    if (typeFilter) {
        typeFilter.addEventListener("change", filterItems);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener("change", filterItems);
    }
    if (statusFilter) {
        statusFilter.addEventListener("change", filterItems);
    }

    // ---------------------------------------------------------
    // 2. Image File Upload Preview
    // ---------------------------------------------------------
    const imageInput = document.querySelector('input[type="file"][name="image"]');
    const previewContainer = document.querySelector(".image-preview-wrapper");

    if (imageInput && previewContainer) {
        imageInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    let previewImg = previewContainer.querySelector("img");
                    if (!previewImg) {
                        previewImg = document.createElement("img");
                        previewContainer.appendChild(previewImg);
                    }
                    previewImg.src = e.target.result;
                    previewContainer.style.display = "flex";
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ---------------------------------------------------------
    // 3. Auto-Dismiss Success Message Toast (Optional)
    // ---------------------------------------------------------
    const alerts = document.querySelectorAll(".success-message");
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(function () {
                if (alert.parentNode) {
                    alert.style.display = "none";
                }
            }, 500);
        }, 5000);
    });
});