// ── Profile dropdown toggle (matches Admin/Restaurant/Deliveryman) ────────

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
