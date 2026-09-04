const roleButtons = document.querySelectorAll(".role-btn");

const userTypeInput = document.getElementById("userType");

const restaurantFields = document.getElementById("restaurantFields");
const deliverymanFields = document.getElementById("deliverymanFields");

const accountForm = document.querySelector(".card");


function showRoleFields(role)
{
    restaurantFields.style.display = "none";
    deliverymanFields.style.display = "none";


    if (role === "restaurant")
    {
        restaurantFields.style.display = "block";
    }

    else if (role === "deliveryman")
    {
        deliverymanFields.style.display = "block";
    }
}


function setActiveRole(role)
{
    roleButtons.forEach(function(button)
    {
        button.classList.remove("active");


        if (button.getAttribute("data-role") === role)
        {
            button.classList.add("active");
        }
    });
}


if (
    roleButtons.length > 0 &&
    userTypeInput &&
    restaurantFields &&
    deliverymanFields
)
{
    roleButtons.forEach(function(button)
    {
        button.addEventListener("click", function()
        {
            const selectedRole = this.getAttribute("data-role");


            userTypeInput.value = selectedRole;


            setActiveRole(selectedRole);


            showRoleFields(selectedRole);
        });
    });


    showRoleFields(userTypeInput.value);


    if (accountForm)
    {
        accountForm.addEventListener("reset", function()
        {
            setTimeout(function()
            {
                const defaultRole = userTypeInput.value;


                setActiveRole(defaultRole);


                showRoleFields(defaultRole);
            }, 0);
        });
    }
}
