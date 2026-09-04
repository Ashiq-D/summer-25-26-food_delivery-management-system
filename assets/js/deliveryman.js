var menuToggle = document.getElementById("menuToggle");
var sidebar = document.getElementById("sidebar");

if (menuToggle && sidebar)
{
    menuToggle.addEventListener("click", function()
    {
        sidebar.classList.toggle("show");
    });
}


var onlineToggle = document.getElementById("onlineToggle");
var onlineStatusText = document.getElementById("onlineStatusText");

if (onlineToggle && onlineStatusText)
{
    onlineToggle.addEventListener("click", function()
    {
        var newStatus;

        if (onlineToggle.classList.contains("active"))
        {
            newStatus = "Offline";
        }

        else
        {
            newStatus = "Online";
        }


        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function()
        {
            if (this.readyState == 4)
            {
                if (this.status == 200)
                {
                    var response = JSON.parse(this.responseText);

                    if (response.success)
                    {
                        if (newStatus == "Online")
                        {
                            onlineToggle.classList.add("active");
                        }

                        else
                        {
                            onlineToggle.classList.remove("active");
                        }

                        onlineStatusText.textContent = newStatus;
                    }

                    else
                    {
                        alert(response.message);
                    }
                }

                else
                {
                    alert("Failed to update online status.");
                }
            }
        };


        xhttp.open("POST", "../../controllers/ajax_controller.php", true);

        xhttp.setRequestHeader(
            "Content-type",
            "application/x-www-form-urlencoded"
        );

        xhttp.send(
            "action=online_status&status=" + encodeURIComponent(newStatus)
        );
    });
}


var startDeliveryBtn = document.getElementById("startDeliveryBtn");
var deliveredBtn = document.getElementById("deliveredBtn");


if (startDeliveryBtn)
{
    startDeliveryBtn.addEventListener("click", function()
    {
        updateDeliveryStatus("start_delivery");
    });
}


if (deliveredBtn)
{
    deliveredBtn.addEventListener("click", function()
    {
        updateDeliveryStatus("delivered");
    });
}


function updateDeliveryStatus(action)
{
    var xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function()
    {
        if (this.readyState == 4)
        {
            if (this.status == 200)
            {
                var response = JSON.parse(this.responseText);

                if (response.success)
                {
                    window.location.reload();
                }

                else
                {
                    alert(response.message);
                }
            }

            else
            {
                alert("Failed to update delivery status.");
            }
        }
    };


    xhttp.open("POST", "../../controllers/ajax_controller.php", true);

    xhttp.setRequestHeader(
        "Content-type",
        "application/x-www-form-urlencoded"
    );

    xhttp.send(
        "action=" + encodeURIComponent(action)
    );
}

var editProfileBtn = document.getElementById("editProfileBtn");
var saveProfileBtn = document.getElementById("saveProfileBtn");

var profileName = document.getElementById("profileName");
var profileEmail = document.getElementById("profileEmail");
var profilePhone = document.getElementById("profilePhone");

var vehicleType = document.getElementById("vehicleType");
var deliveryArea = document.getElementById("deliveryArea");


if (
    editProfileBtn &&
    saveProfileBtn &&
    profileName &&
    profileEmail &&
    profilePhone &&
    vehicleType &&
    deliveryArea
)
{
    editProfileBtn.addEventListener("click", function()
    {
        profileName.readOnly = false;
        profileEmail.readOnly = false;
        profilePhone.readOnly = false;

        vehicleType.disabled = false;
        deliveryArea.disabled = false;

        saveProfileBtn.style.display = "inline-block";
        editProfileBtn.style.display = "none";
    });
}


var profileForm = document.getElementById("profileForm");

if (profileForm)
{
    profileForm.addEventListener("submit", function(event)
    {
        if (!validateProfileForm())
        {
            event.preventDefault();
        }
    });
}


function validateProfileForm()
{
    var profileName = document.getElementById("profileName");
    var profileEmail = document.getElementById("profileEmail");
    var profilePhone = document.getElementById("profilePhone");

    var vehicleType = document.getElementById("vehicleType");
    var deliveryArea = document.getElementById("deliveryArea");

    var nameError = document.getElementById("nameError");
    var emailError = document.getElementById("emailError");
    var phoneError = document.getElementById("phoneError");

    var vehicleError = document.getElementById("vehicleError");
    var areaError = document.getElementById("areaError");

    var valid = true;


    nameError.textContent = "";
    emailError.textContent = "";
    phoneError.textContent = "";
    vehicleError.textContent = "";

    if (areaError)
    {
        areaError.textContent = "";
    }


    var name = profileName.value.trim();
    var email = profileEmail.value.trim();
    var phone = profilePhone.value.trim();
    var vehicle = vehicleType.value.trim();


    var namePattern = /^[A-Za-z-' ]+$/;

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    var phonePattern = /^01[3-9][0-9]{8}$/;


    if (name == "")
    {
        nameError.textContent = "Name is required.";

        valid = false;
    }

    else if (!namePattern.test(name))
    {
        nameError.textContent = "Enter a valid name.";

        valid = false;
    }


    if (email == "")
    {
        emailError.textContent = "Email is required.";

        valid = false;
    }

    else if (!emailPattern.test(email))
    {
        emailError.textContent = "Enter a valid email address.";

        valid = false;
    }


    if (phone == "")
    {
        phoneError.textContent = "Phone number is required.";

        valid = false;
    }

    else if (!phonePattern.test(phone))
    {
        phoneError.textContent = "Enter a valid Bangladeshi phone number.";

        valid = false;
    }


    if (vehicle == "")
    {
        vehicleError.textContent = "Vehicle type is required.";

        valid = false;
    }


    if (deliveryArea && deliveryArea.value == "")
    {
        areaError.textContent = "Please select an area.";

        valid = false;
    }


    return valid;
}