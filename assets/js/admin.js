var menuToggle = document.getElementById("menuToggle");
var sidebar = document.getElementById("sidebar");

if (menuToggle && sidebar)
{
    menuToggle.addEventListener("click", function()
    {
        sidebar.classList.toggle("show");
    });
}


var profileToggle = document.getElementById("profileToggle");
var profileDropdown = document.getElementById("profileDropdown");

if (profileToggle && profileDropdown)
{
    profileToggle.addEventListener("click", function(event)
    {
        event.stopPropagation();
        profileDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function(event)
    {
        if (!profileToggle.contains(event.target))
        {
            profileDropdown.classList.remove("show");
        }
    });
}


var adminSearch = document.getElementById("adminSearch");

if (adminSearch)
{
    adminSearch.addEventListener("keyup", function()
    {
        var query = adminSearch.value.trim().toLowerCase();
        var tables = document.querySelectorAll(".main-content table.data-table");

        tables.forEach(function(table)
        {
            var rows = table.querySelectorAll("tbody tr");

            rows.forEach(function(row)
            {
                var rowText = row.textContent.toLowerCase();
                row.style.display = rowText.indexOf(query) !== -1 ? "" : "none";
            });
        });
    });
}
