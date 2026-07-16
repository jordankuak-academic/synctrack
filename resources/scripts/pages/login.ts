import Controller from "@/utils/controller";

type AuthMode = "login" | "signup";

export default class LoginController extends Controller {
  protected initialize(): void {
    this.querySelectorAll<HTMLButtonElement>("[data-auth-tab]").forEach((tab) => {
      tab.addEventListener("click", () => {
        this.setAuthMode(tab.dataset.authTab as AuthMode);
      });
    });

    const nextButton = this.querySelector<HTMLButtonElement>("[data-signup-next]");
    nextButton?.addEventListener("click", () => this.showSecondSignupStep());

    const confirmation = this.querySelector<HTMLInputElement>("#signup-password-confirmation");
    confirmation?.addEventListener("input", () => confirmation.setCustomValidity(""));

    this.querySelectorAll<HTMLElement>(".password-toggle-icon").forEach((toggle) => {
      toggle.addEventListener("click", () => {
        const inputId = toggle.dataset.togglePassword;
        if (!inputId) return;
        const input = this.querySelector<HTMLInputElement>(`#${inputId}`);
        if (!input) return;

        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";

        const eyeIcon = toggle.querySelector(".icon-eye");
        const eyeOffIcon = toggle.querySelector(".icon-eye-off");
        if (eyeIcon && eyeOffIcon) {
          if (input.type === "password") {
            eyeIcon.classList.add("hidden");
            eyeOffIcon.classList.remove("hidden");
          } else {
            eyeIcon.classList.remove("hidden");
            eyeOffIcon.classList.add("hidden");
          }
        }
      });
    });
  }

  private setAuthMode(mode: AuthMode): void {
    this.querySelectorAll<HTMLButtonElement>("[data-auth-tab]").forEach((tab) => {
      const isActive = tab.dataset.authTab === mode;
      tab.classList.toggle("login-card__tab--active", isActive);
      tab.setAttribute("aria-selected", String(isActive));
    });

    this.querySelectorAll<HTMLElement>("[data-auth-panel]").forEach((panel) => {
      panel.hidden = panel.dataset.authPanel !== mode;
    });

    this.querySelectorAll<HTMLButtonElement>("[data-auth-submit]").forEach((button) => {
      button.hidden = button.dataset.authSubmit !== mode || (button.dataset.signupSubmit === "2");
    });
  }

  private showSecondSignupStep(): void {
    const password = this.querySelector<HTMLInputElement>("#signup-password");
    const confirmation = this.querySelector<HTMLInputElement>("#signup-password-confirmation");
    const stepOneFields = this.querySelectorAll<HTMLInputElement>("[data-signup-step='1'] input[required]");

    if (password && confirmation) {
      confirmation.setCustomValidity(password.value === confirmation.value ? "" : "Passwords do not match.");
    }

    const isValid = Array.from(stepOneFields).every((field) => field.reportValidity());
    if (!isValid) {
      return;
    }

    this.querySelectorAll<HTMLElement>("[data-signup-step]").forEach((step) => {
      step.hidden = step.dataset.signupStep !== "2";
    });

    this.querySelectorAll<HTMLButtonElement>("[data-signup-submit]").forEach((button) => {
      button.hidden = button.dataset.signupSubmit !== "2";
    });
  }
}



