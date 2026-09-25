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
     Enquiry forms — inline validation, in-place submission, and (on the
     growth-audit form) one section at a time with a progress bar.
     Without JS the form still posts normally and the server re-renders it.
     --------------------------------------------------------------------- */
  var form = document.querySelector("form[data-enquiry]");

  if (form && window.fetch && window.FormData) {
    var status = form.querySelector("[data-status]");
    var statusText = form.querySelector("[data-status-text]");
    var submit = form.querySelector("[data-submit]");
    var submitLabel = submit.querySelector("[data-submit-label]");
    var idleLabel = submitLabel ? submitLabel.textContent : "";
    var contactEmail = form.getAttribute("data-email") || "";
    var contactPhone = form.getAttribute("data-phone") || "";
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var PHONE_RE = /^[+\d][\d\s().-]{5,24}$/;
    var URL_RE = /^(https?:\/\/)?[a-z0-9-]+(\.[a-z0-9-]+)+([\/?#].*)?$/i;

    // The same rules the server applies, read off the input's own attributes
    // (required, type, maxlength), so the visitor is told before the round
    // trip rather than after it.
    function ruleFor(input) {
      if (input.type === "checkbox" || input.type === "hidden") return "";
      var value = input.value.trim();
      if (!value) {
        if (!input.required) return "";
        return input.getAttribute("data-required") ||
          (input.tagName === "SELECT" ? "Please choose an option." : "Please fill this in.");
      }
      var max = parseInt(input.getAttribute("maxlength"), 10);
      if (max && value.length > max) return "Please keep this under " + max.toLocaleString() + " characters.";
      if (input.type === "email" && !EMAIL_RE.test(value)) {
        return "That email address does not look right. Check for a typo, e.g. name@company.com.";
      }
      if (input.type === "tel" && !PHONE_RE.test(value)) {
        return "That phone number does not look right. Digits, spaces and + are fine.";
      }
      if (input.type === "url" && !URL_RE.test(value)) {
        return "That web address does not look right, e.g. https://yourcompany.com.";
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
      form.querySelectorAll("input:not([type=hidden]):not([type=checkbox]):not(#website), select, textarea")
    );

    inputs.forEach(function (input) {
      // Judge on blur, then keep re-judging as they fix it.
      input.addEventListener("blur", function () {
        if (input.value.trim() || input.required) validate(input);
      });
      input.addEventListener("input", function () {
        if (input.getAttribute("aria-invalid") === "true") validate(input);
      });
      input.addEventListener("change", function () {
        if (input.getAttribute("aria-invalid") === "true") validate(input);
      });
    });

    /* Steps: only the growth-audit form has more than one [data-step]. */
    var steps = Array.prototype.slice.call(form.querySelectorAll("[data-step]"));
    var progress = Array.prototype.slice.call(form.querySelectorAll("[data-progress-item]"));
    var finalActions = form.querySelector(".form-step__actions--final");
    var stepped = steps.length > 1;
    var current = 0;

    function stepOf(input) {
      var step = input.closest("[data-step]");
      return step ? steps.indexOf(step) : steps.length - 1;
    }

    function showStep(index, focusFirst) {
      if (!stepped) return;
      current = Math.max(0, Math.min(index, steps.length - 1));
      var last = current === steps.length - 1;
      steps.forEach(function (step, n) {
        step.hidden = n !== current;
        step.classList.toggle("is-active", n === current);
      });
      progress.forEach(function (item, n) {
        item.classList.toggle("is-current", n === current);
        item.classList.toggle("is-done", n < current);
        if (n === current) item.setAttribute("aria-current", "step");
        else item.removeAttribute("aria-current");
      });
      if (finalActions) finalActions.hidden = !last;
      if (focusFirst) {
        var top = form.getBoundingClientRect().top + window.pageYOffset - 96;
        window.scrollTo({ top: top, behavior: "smooth" });
        var first = steps[current].querySelector("input:not([type=hidden]):not([type=checkbox]), select, textarea");
        if (first) first.focus({ preventScroll: true });
      }
    }

    function validateStep(index) {
      var firstBad = null;
      inputs.forEach(function (input) {
        if (stepOf(input) !== index) return;
        if (!validate(input) && !firstBad) firstBad = input;
      });
      return firstBad;
    }

    if (stepped) {
      form.classList.add("form--stepped");
      form.addEventListener("click", function (event) {
        var next = event.target.closest("[data-step-next]");
        var back = event.target.closest("[data-step-back]");
        if (next) {
          var bad = validateStep(current);
          if (bad) {
            showStatus("error", "Please fix the highlighted field" + (steps[current].querySelectorAll(".field--invalid").length > 1 ? "s" : "") + " before continuing.");
            bad.focus();
            return;
          }
          hideStatus();
          showStep(current + 1, true);
        } else if (back) {
          hideStatus();
          showStep(current - 1, true);
        }
      });
      // After a failed no-JS post the server marks the bad field: open its step.
      var serverBad = form.querySelector(".field--invalid input, .field--invalid select, .field--invalid textarea");
      showStep(serverBad ? stepOf(serverBad) : 0, false);
    }

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
      if (submitLabel) submitLabel.textContent = busy ? "Sending…" : idleLabel;
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      hideStatus();

      // Enter in a text field on an earlier step means "continue", not "send".
      if (stepped && current < steps.length - 1) {
        var stepBad = validateStep(current);
        if (stepBad) { stepBad.focus(); return; }
        showStep(current + 1, true);
        return;
      }

      var firstBad = null;
      inputs.forEach(function (input) {
        if (!validate(input) && !firstBad) firstBad = input;
      });
      if (firstBad) {
        showStep(stepOf(firstBad), false);
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
          inputs.forEach(function (input) { setError(input, ""); });
          form.reset();
          showStep(0, false);
          showStatus(data.tone || "ok", escapeHtml(data.text || "Thanks — your request is with the team."));
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
          showStep(stepOf(focusTarget), false);
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
