import jQuery from 'jquery';

jQuery(document).ready(function ($) {
  "use strict";

  var $root = $("#builton-contact");
  if (!$root.length) return;

  /* ---------------------------------------------------------------
     Office clocks — live local time for each office, ticking every
     second off each element's data-builton-tz timezone.
  --------------------------------------------------------------- */
  var $clocks = $root.find("[data-builton-tz]");

  function updateClocks() {
    var formatOptions = { hour: "2-digit", minute: "2-digit", hour12: false };
    $clocks.each(function () {
      var $clock = $(this);
      var tz = $clock.attr("data-builton-tz");
      var time = new Intl.DateTimeFormat("en-US", $.extend({ timeZone: tz }, formatOptions)).format(new Date());
      $clock.text(time);
    });
  }

  if ($clocks.length) {
    updateClocks();
    setInterval(updateClocks, 1000);
  }

  /* ---------------------------------------------------------------
     Click-to-copy — email and phone number quick-contact rows.
  --------------------------------------------------------------- */
  var $copyButtons = $root.find("[data-copy]");
  var $copiedLabel = $root.find(".builton-contact-info__copied");
  var copiedTimer = null;

  function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text);
    }
    // Fallback for browsers/contexts without the Clipboard API (e.g. non-HTTPS).
    var $temp = $("<textarea readonly></textarea>").css({ position: "fixed", opacity: 0 }).val(text);
    $("body").append($temp);
    $temp[0].select();
    document.execCommand("copy");
    $temp.remove();
    return $.Deferred().resolve().promise();
  }

  $copyButtons.on("click", function () {
    var text = $(this).attr("data-copy");
    if (!text) return;

    copyToClipboard(text).then(showCopied);

    function showCopied() {
      $copiedLabel.text("Copied!").addClass("is-visible");
      clearTimeout(copiedTimer);
      copiedTimer = setTimeout(function () {
        $copiedLabel.removeClass("is-visible");
      }, 1500);
    }
  });

  /* ---------------------------------------------------------------
     Contact form — client-side validation + submit.

     SUBMIT_ENDPOINT comes from the form's data-endpoint attribute in
     contact.html. Point it at your WP handler:
       - an admin-ajax.php action (add_action('wp_ajax_...')/
         'wp_ajax_nopriv_...') with action=<name> in the posted data, or
       - a custom REST route, or
       - a form plugin's own submission endpoint.
     Left empty, the form is still fully validated and interactive, but
     skips the network call and shows the success state directly — useful
     for previewing the page before the backend is wired up. Once an
     endpoint is set this falls through to a real $.ajax POST.
  --------------------------------------------------------------- */
  var $form = $root.find("#builton-contact-form");
  var $success = $root.find(".builton-contact-form__success");
  var $error = $root.find(".builton-contact-form__error");
  var SUBMIT_ENDPOINT = $form.attr("data-endpoint");

  $form.on("submit", function (e) {
    e.preventDefault();

    if (!this.checkValidity()) {
      this.reportValidity();
      return;
    }

    var $submit = $form.find(".builton-contact-form__submit");
    $submit.prop("disabled", true);
    $error.attr("hidden", true);

    function onSuccess() {
      $form.attr("hidden", true);
      $success.removeAttr("hidden");
    }

    function onFail() {
      $error.removeAttr("hidden");
      $submit.prop("disabled", false);
    }

    if (!SUBMIT_ENDPOINT) {
      onSuccess();
      return;
    }

    $.ajax({
      url: SUBMIT_ENDPOINT,
      method: "POST",
      data: $form.serialize(),
    }).then(onSuccess, onFail);
  });

  /* ---------------------------------------------------------------
     Title entrance — masks the "Contact" heading in once it scrolls
     into view, matching the same reveal used on the Team page.
  --------------------------------------------------------------- */
  var titleEl = $root.find(".builton-contact-title")[0];

  if (titleEl) {
    if (typeof window.IntersectionObserver === "function") {
      var titleObserver = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add("is-revealed");
            titleObserver.unobserve(entry.target);
          });
        },
        { threshold: 0.15, rootMargin: "0px 0px -10% 0px" }
      );
      titleObserver.observe(titleEl);
    } else {
      // No IntersectionObserver support: just show the final state
      // rather than leaving the heading permanently hidden.
      titleEl.classList.add("is-revealed");
    }
  }
});
