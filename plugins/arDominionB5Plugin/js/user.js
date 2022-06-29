(($) => {
  "use strict";

  const $element = $("input.password-strength[type=password]");
  $(() => new PasswordStrength($element));

  class PasswordStrength {
    constructor($element) {
      if (!$element.length) {
        return;
      }

      this.$form = $element.parents("form");
      this.$passwordInput = $element;
      this.$confirmInput = $("input.password-confirm", this.$form);
      this.settings = this.$form
        .find(".password-strength-settings")
        .get(0).dataset;
      this.requireStrongPassword = !!JSON.parse(
        this.settings.requireStrongPassword
      );

      // Prevent the form from running its validation logic when the form is
      // submitted. Otherwise our submit handler will not be executed if
      // native validation kicks in and finds a validation issue.
      this.$form.attr("novalidate", "novalidate");

      this.$progressBar = this.$form
        .find(".template")
        .removeAttr("hidden")
        .find(".progress-bar");

      this.$passwordInput.on("focus input", this.passwordCheck.bind(this));
      this.$form.on("submit", this.submit.bind(this));
    }

    passwordCheck(event) {
      const input = this.$passwordInput.get(0);
      const password = input.value;
      const score = PasswordStrength.score(
        password,
        this.settings.username,
        this.settings
      );

      // Update the progress bar.
      this.$progressBar
        .css("width", score.strength + "%")
        .attr("aria-valuenow", score.strength)
        .removeClass()
        .addClass(() => {
          const classes = ["progress-bar"];
          if (score.strength < 50) {
            classes.push("bg-danger");
          } else if (score.strength < 100) {
            classes.push("bg-warning");
          } else {
            classes.push("bg-success");
          }
          return classes;
        });

      // Inject password hints.
      const $container = this.$progressBar.parent().parent();
      $container.children("ul").remove();
      score.strength < 100 &&
        $container.append(score.message).find("ul").addClass("text-danger");

      input.setCustomValidity(
        score.strength < 100 ? this.settings.notStrong : ""
      );
    }

    // Update the validity state of the confirm input.
    passwordMatchCheck() {
      const input = this.$confirmInput.get(0);
      const password = this.$passwordInput.val();

      input.setCustomValidity("");

      if (!password.length) {
        return;
      }

      if (password != input.value) {
        input.setCustomValidity(this.settings.confirmFailure);
      }
    }

    // Prevent submission when fields are invalid.
    submit(event) {
      let input = this.$form.find("input[name=email]").get(0);
      if (input && input.validity.typeMismatch) {
        input.reportValidity();
        event.preventDefault();
        return;
      }

      input = this.$form.find("input[name=password]").get(0);
      if (!input.validity.valid && this.requireStrongPassword) {
        input.reportValidity();
        event.preventDefault();
        return;
      }

      this.passwordMatchCheck();
      input = this.$confirmInput.get(0);
      if (!input.validity.valid) {
        input.reportValidity();
        event.preventDefault();
        return;
      }
    }

    static score(password, username, translate) {
      var weaknesses = 0,
        strength = 100,
        msg = [];

      var hasLowercase = password.match(/[a-z]+/);
      var hasUppercase = password.match(/[A-Z]+/);
      var hasNumbers = password.match(/[0-9]+/);
      var hasPunctuation = password.match(/[^a-zA-Z0-9]+/);

      // Lose 5 points for every character less than 6, plus a 30 point penalty.
      if (password.length < 6) {
        msg.push(translate.tooShort);
        strength -= (6 - password.length) * 5 + 30;
      }

      // Count weaknesses.
      if (!hasLowercase) {
        msg.push(translate.addLowerCase);
        weaknesses++;
      }
      if (!hasUppercase) {
        msg.push(translate.addUpperCase);
        weaknesses++;
      }
      if (!hasNumbers) {
        msg.push(translate.addNumbers);
        weaknesses++;
      }
      if (!hasPunctuation) {
        msg.push(translate.addPunctuation);
        weaknesses++;
      }

      // Apply penalty for each weakness (balanced against length penalty).
      switch (weaknesses) {
        case 1:
          strength -= 12.5;
          break;

        case 2:
          strength -= 25;
          break;

        case 3:
          strength -= 40;
          break;

        case 4:
          strength -= 40;
          break;
      }

      // Check if password is the same as the username.
      if (
        password !== "" &&
        password.toLowerCase() === username.toLowerCase()
      ) {
        msg.push(translate.sameAsUsername);
        // Passwords the same as username are always very weak.
        strength = 5;
      }

      // Assemble the final message.
      msg = "<ul><li>" + msg.join("</li><li>") + "</li></ul>";
      return { strength: strength, message: msg };
    }
  }
})(jQuery);
