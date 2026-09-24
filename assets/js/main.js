/* ClientcareX — front-end behaviour. No dependencies. */
(function () {
  "use strict";

  /* ---------------------------------------------------------------------
     Mobile navigation
     --------------------------------------------------------------------- */
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.getElementById("primary-nav");

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", String(!open));
      nav.setAttribute("data-open", String(!open));
    });

    // Close when a link is chosen, or on Escape.
    nav.addEventListener("click", function (event) {
      if (event.target.closest("a")) closeNav();
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") closeNav();
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 1020) closeNav();
    });
  }

  function closeNav() {
    if (!toggle || !nav) return;
    toggle.setAttribute("aria-expanded", "false");
    nav.setAttribute("data-open", "false");
  }

  /* ---------------------------------------------------------------------
     FAQ — keep one answer open at a time within a group
     --------------------------------------------------------------------- */
  document.querySelectorAll(".faq").forEach(function (group) {
    var items = group.querySelectorAll("details.faq__item");
    items.forEach(function (item) {
      item.addEventListener("toggle", function () {
        if (!item.open) return;
        items.forEach(function (other) {
          if (other !== item) other.open = false;
        });
      });
    });
  });

  /* ---------------------------------------------------------------------
     Scroll reveal
     --------------------------------------------------------------------- */
  var revealables = document.querySelectorAll("[data-reveal]");

  if (!("IntersectionObserver" in window) ||
      window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    revealables.forEach(function (el) { el.classList.add("is-visible"); });
  } else {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });

    revealables.forEach(function (el) { observer.observe(el); });
  }

  /* ---------------------------------------------------------------------
     Enquiry form — sent in the background, answered in place
     --------------------------------------------------------------------- */
  var enquiry = document.getElementById("enquiry");

  if (enquiry && window.fetch) {
    var status = document.getElementById("enquiry-status");
    var done = document.getElementById("enquiry-done");
    var button = enquiry.querySelector('button[type="submit"]');
    var label = button.textContent;

    var say = function (text) {
      status.textContent = text;
      status.hidden = !text;
      if (text) status.scrollIntoView({ behavior: "smooth", block: "center" });
    };

    var markField = function (name, text) {
      var input = enquiry.querySelector('[name="' + name + '"]');
      var hint = enquiry.querySelector('[data-error-for="' + name + '"]');
      if (input) {
        if (text) input.setAttribute("aria-invalid", "true");
        else input.removeAttribute("aria-invalid");
      }
      if (hint) {
        hint.textContent = text || "";
        hint.hidden = !text;
      }
    };

    enquiry.addEventListener("submit", function (event) {
      event.preventDefault();

      var name = enquiry.elements.name.value.trim();
      var email = enquiry.elements.email.value.trim();
      var errors = {
        name: name ? "" : "Please tell us your name.",
        email: /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email) ? "" : "Please enter a valid work email."
      };
      markField("name", errors.name);
      markField("email", errors.email);
      if (errors.name || errors.email) {
        say("");
        enquiry.querySelector('[aria-invalid="true"]').focus();
        return;
      }

      button.disabled = true;
      button.textContent = "Sending…";
      say("");

      fetch(enquiry.action, {
        method: "POST",
        body: new FormData(enquiry),
        headers: { "X-Requested-With": "fetch", Accept: "application/json" },
        credentials: "same-origin"
      })
        .then(function (response) { return response.json().catch(function () { return null; }); })
        .then(function (data) {
          button.disabled = false;
          button.textContent = label;

          if (data && data.ok) {
            enquiry.reset();
            enquiry.hidden = true;
            done.hidden = false;
            done.scrollIntoView({ behavior: "smooth", block: "center" });
            return;
          }

          var fieldErrors = (data && data.errors) || {};
          ["name", "email", "message"].forEach(function (key) { markField(key, fieldErrors[key]); });
          say((data && data.message) || "We could not send your request. Please try again, or email care@clientcarex.com.");
        })
        .catch(function () {
          button.disabled = false;
          button.textContent = label;
          say("Something went wrong on the way. Please try again, or email care@clientcarex.com.");
        });
    });
  }

})();
