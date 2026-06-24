(function () {
  function addDetailsLabel() {
    var container = document.querySelector("#wpforms-247 .wpforms-field-container");
    var nameField = document.getElementById("wpforms-247-field_1-container");

    if (!container || !nameField || document.getElementById("wpforms-247-details-label")) {
      return;
    }

    var heading = document.createElement("div");
    heading.id = "wpforms-247-details-label";
    heading.className = "wpf-details-label";
    heading.textContent = "Your details";

    container.insertBefore(heading, nameField);

    // Adds an announced description on top of each field's own label
    // (Name/Email keep their own accessible name) rather than replacing it.
    ["#wpforms-247-field_1", "#wpforms-247-field_1-last", "#wpforms-247-field_2", "#wpforms-247 .wpforms-field-phone input"].forEach(
      function (selector) {
        var input = document.querySelector(selector);
        if (input) {
          input.setAttribute("aria-describedby", "wpforms-247-details-label");
        }
      }
    );
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", addDetailsLabel);
  } else {
    addDetailsLabel();
  }
})();
