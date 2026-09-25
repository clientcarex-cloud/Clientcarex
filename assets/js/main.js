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
        // Also reveal anything already above the viewport — a jump to an
        // anchor (e.g. /contact#enquiry) can skip straight past it.
        if (!entry.isIntersecting && entry.boundingClientRect.bottom > 0) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });

    revealables.forEach(function (el) { observer.observe(el); });
  }

  /* ---------------------------------------------------------------------
     Growth-audit form — inline validation and in-place submission.
     Without JS the form still posts normally and the server re-renders it.
     --------------------------------------------------------------------- */
  var form = document.querySelector("form[data-enquiry]");

  if (form && window.fetch && window.FormData) {
    var status = form.querySelector("[data-status]");
    var statusText = form.querySelector("[data-status-text]");
    var submit = form.querySelector("[data-submit]");
    var contactEmail = form.getAttribute("data-email") || "";
    var contactPhone = form.getAttribute("data-phone") || "";
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var PHONE_RE = /^[+\d][\d\s().-]{5,24}$/;

    // The same rules the server applies, so the visitor is told before the
    // round trip rather than after it.
    function ruleFor(input) {
      var value = input.value.trim();
      var name = input.name;
      if (name === "name") {
        if (!value) return "Please tell us your name.";
        if (value.length > 120) return "Please keep your name under 120 characters.";
      }
      if (name === "email") {
        if (!value) return "Please enter your work email so we can reply.";
        if (!EMAIL_RE.test(value)) return "That email address does not look right. Check for a typo, e.g. name@company.com.";
      }
      if (name === "phone" && value && !PHONE_RE.test(value)) {
        return "That phone number does not look right. Digits, spaces and + are fine.";
      }
      if (name === "message" && value.length > 5000) {
        return "Please keep the message under 5,000 characters.";
      }
      return "";
    }

    function fieldOf(input) { return input.closest(".field"); }
    function errorSlot(input) {
      var field = fieldOf(input);
      return field ? field.querySelector(".field__error") : null;
    }

    function setError(input, message) {
      var field = fieldOf(input);
      var slot = errorSlot(input);
      if (!field || !slot) return;
      slot.textContent = message || "";
      field.classList.toggle("field--invalid", !!message);
      if (message) input.setAttribute("aria-invalid", "true");
      else input.removeAttribute("aria-invalid");
    }

    function validate(input) {
      var message = ruleFor(input);
      setError(input, message);
      return !message;
    }

    var inputs = Array.prototype.slice.call(
      form.querySelectorAll("input:not([type=hidden]):not(#website), select, textarea")
    );

    inputs.forEach(function (input) {
      // Judge on blur, then keep re-judging as they fix it.
      input.addEventListener("blur", function () {
        if (input.value.trim() || input.required) validate(input);
      });
      input.addEventListener("input", function () {
        if (input.getAttribute("aria-invalid") === "true") validate(input);
      });
    });

    function showStatus(tone, html) {
      status.className = "form__status" + (tone ? " form__status--" + tone : "");
      status.setAttribute("role", tone === "ok" ? "status" : "alert");
      statusText.innerHTML = html;
      status.hidden = false;
      status.scrollIntoView({ block: "center", behavior: "smooth" });
    }

    function hideStatus() {
      status.hidden = true;
      statusText.textContent = "";
    }

    function escapeHtml(text) {
      return String(text).replace(/[&<>"']/g, function (c) {
        return { "&": "&amp;", "<": "&lt;", ">": "&gt;", "\"": "&quot;", "'": "&#39;" }[c];
      });
    }

    function fallbackContact() {
      var parts = [];
      if (contactEmail) parts.push('email <a href="mailto:' + escapeHtml(contactEmail) + '">' + escapeHtml(contactEmail) + "</a>");
      if (contactPhone) parts.push('call <a href="tel:' + escapeHtml(contactPhone.replace(/\s+/g, "")) + '">' + escapeHtml(contactPhone) + "</a>");
      return parts.length ? " You can also " + parts.join(" or ") + "." : "";
    }

    function setBusy(busy) {
      submit.setAttribute("aria-busy", busy ? "true" : "false");
      submit.disabled = busy;
      var label = submit.querySelector("[data-submit-label]");
      if (label) label.textContent = busy ? "Sending…" : "Get my free growth audit";
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      hideStatus();

      var firstBad = null;
      inputs.forEach(function (input) {
        if (!validate(input) && !firstBad) firstBad = input;
      });
      if (firstBad) {
        showStatus("error", "Please fix the highlighted field" + (form.querySelectorAll(".field--invalid").length > 1 ? "s" : "") + " and try again.");
        firstBad.focus();
        return;
      }

      setBusy(true);

      var controller = window.AbortController ? new AbortController() : null;
      var timer = controller && setTimeout(function () { controller.abort(); }, 20000);

      fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        credentials: "same-origin",
        headers: { "Accept": "application/json", "X-Requested-With": "fetch" },
        signal: controller ? controller.signal : undefined
      }).then(function (response) {
        return response.json().then(function (data) {
          return { http: response.status, data: data };
        }, function () {
          throw new Error("The server replied with something unexpected (HTTP " + response.status + ").");
        });
      }).then(function (result) {
        var data = result.data;
        if (data.ok) {
          showStatus(data.tone || "ok", escapeHtml(data.text || "Thanks — your request is with the team."));
          inputs.forEach(function (input) { setError(input, ""); });
          form.reset();
          return;
        }
        var errors = data.errors || {};
        var focusTarget = null;
        inputs.forEach(function (input) {
          setError(input, errors[input.name] || "");
          if (errors[input.name] && !focusTarget) focusTarget = input;
        });
        if (errors.form) {
          showStatus("error", escapeHtml(errors.form));
        } else if (focusTarget) {
          showStatus("error", "Please fix the highlighted field" + (Object.keys(errors).length > 1 ? "s" : "") + " and try again.");
        } else {
          showStatus("error", "Something went wrong on our side (HTTP " + result.http + ")." + fallbackContact());
        }
        if (focusTarget) focusTarget.focus();
      }).catch(function (error) {
        var reason = error && error.name === "AbortError"
          ? "The request timed out."
          : (navigator.onLine === false ? "You appear to be offline." : "We couldn't reach the server (" + escapeHtml(error && error.message || "network error") + ").");
        showStatus("error", reason + " Your message was not sent — please try again." + fallbackContact());
      }).then(function () {
        if (timer) clearTimeout(timer);
        setBusy(false);
      });
    });
  }

})();
