import jQuery from 'jquery';

jQuery(document).ready(function ($) {
  "use strict";

  var $root = $("#builton-team");
  if (!$root.length) return;

  // easeOutExpo, the same easing family the original site uses for the
  // click-to-expand bio animation below. Registering it on $.easing lets
  // jQuery's built-in .animate() use it by name, with no jQuery
  // UI/easing plugin required.
  $.easing.tandemOutExpo = function (t) {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
  };

  /* ---------- Click-to-expand bio ---------- */

  var prefersReducedMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  $root.find(".builton-team-card__caption").each(function () {
    var $caption = $(this);
    var $card = $caption.closest(".builton-team-card");
    var $bio = $card.find(".builton-team-card__bio");
    if (!$bio.length) return;

    function toggleBio() {
      var opening = !$card.hasClass("is-open");
      $card.toggleClass("is-open", opening);
      $caption.attr("aria-expanded", opening ? "true" : "false");

      if (prefersReducedMotion) {
        $bio.stop(true).css("height", opening ? $bio[0].scrollHeight : 0);
        return;
      }

      if (opening) {
        var targetHeight = $bio[0].scrollHeight;
        $bio
          .stop(true)
          .animate({ height: targetHeight }, 500, "tandemOutExpo");
      } else {
        $bio.stop(true).animate({ height: 0 }, 1400, "tandemOutExpo");
      }
    }

    $caption.on("click.tandemTeam", toggleBio);

    $caption.on("keydown.tandemTeam", function (event) {
      if (event.key === "Enter" || event.key === " " || event.key === "Spacebar") {
        event.preventDefault();
        toggleBio();
      }
    });
  });

  /* ---------- Scroll-triggered entrance reveals ---------- */

  var revealTargets = $root
    .find(".builton-team-title, .builton-team-card")
    .toArray();

  if (!revealTargets.length) return;

  if (typeof window.IntersectionObserver === "function") {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-revealed");
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.15,
        rootMargin: "0px 0px -10% 0px",
      }
    );

    revealTargets.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    // No IntersectionObserver support: just show everything in its final
    // state rather than leaving it permanently hidden.
    revealTargets.forEach(function (el) {
      el.classList.add("is-revealed");
    });
  }
});
