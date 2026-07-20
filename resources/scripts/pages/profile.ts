import Controller from "@/utils/controller";

type ProfileMode = "view" | "edit" | "password";

export default class ProfileController extends Controller {
  protected initialize(): void {
    this.bindEvents();
    this.setMode((this.rootElement.dataset.initialMode as ProfileMode) || "view");
    const notification = this.querySelector<HTMLElement>("[data-notification]");
    if (notification) window.setTimeout(() => this.dismissNotification(notification), 3000);
  }

  private bindEvents(): void {
    this.rootElement.addEventListener("click", (event) => {
      const actionButton = (event.target as Element).closest<HTMLButtonElement>("[data-action]");
      if (!actionButton) return;

      switch (actionButton.dataset.action) {
        case "edit":
          this.setMode("edit");
          break;
        case "password":
          this.setMode("password");
          this.querySelector<HTMLInputElement>("#new_password")?.focus();
          break;
        case "cancel-password":
          this.closePasswordModal();
          break;
      }
    });
    this.addEventListener("[data-dismiss-notification]", "click", () => {
      const notification = this.querySelector<HTMLElement>("[data-notification]");
      if (notification) this.dismissNotification(notification);
    });
    this.querySelectorAll<HTMLButtonElement>("[data-password-toggle]").forEach((button) => button.addEventListener("click", () => this.togglePassword(button)));
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !this.querySelector<HTMLElement>("[data-password-panel]")?.hidden) {
        this.closePasswordModal();
      }
    });
    this.querySelector<HTMLFormElement>("[data-password-form]")?.addEventListener("submit", (event) => {
      const password = this.querySelector<HTMLInputElement>("#new_password");
      const confirmation = this.querySelector<HTMLInputElement>("#new_password_confirmation");
      if (password && confirmation && password.value !== confirmation.value) {
        event.preventDefault();
        this.showClientError("Password different. Please try again.");
      }
    });
  }

  private closePasswordModal(): void {
    this.querySelector<HTMLFormElement>("[data-password-form]")?.reset();
    this.querySelectorAll<HTMLButtonElement>("[data-password-toggle]").forEach((button) => {
      const input = this.querySelector<HTMLInputElement>(`#${button.dataset.target}`);
      if (input) input.type = "password";
      button.classList.remove("is-visible");
      button.setAttribute("aria-label", "Show password");
      button.setAttribute("aria-pressed", "false");
    });
    this.setMode("view");
    this.querySelector<HTMLButtonElement>("[data-action='password']")?.focus();
  }

  private setMode(mode: ProfileMode): void {
    const edit = mode === "edit";
    this.querySelectorAll<HTMLElement>("[data-edit-input]").forEach((el) => el.hidden = !edit);
    this.querySelectorAll<HTMLElement>("[data-view-value]").forEach((el) => el.hidden = edit);
    this.toggle("[data-action='edit']", mode !== "view");
    this.toggle("[data-edit-back]", !edit);
    this.toggle("[data-action='save-profile']", !edit);
    this.toggle("[data-action='password']", mode === "password");
    this.toggle("[data-password-panel]", mode !== "password");
  }

  private toggle(selector: string, hidden: boolean): void {
    const element = this.querySelector<HTMLElement>(selector);
    if (element) element.hidden = hidden;
  }

  private togglePassword(button: HTMLButtonElement): void {
    const input = this.querySelector<HTMLInputElement>(`#${button.dataset.target}`);
    if (!input) return;
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    button.classList.toggle("is-visible", show);
    button.setAttribute("aria-label", `${show ? "Hide" : "Show"} password`);
    button.setAttribute("aria-pressed", String(show));
  }

  private showClientError(message: string): void {
    this.querySelector("[data-notification]")?.remove();
    const alert = document.createElement("div");
    alert.className = "profile-notification profile-notification--error";
    alert.dataset.notification = "";
    alert.setAttribute("role", "alert");
    alert.textContent = message;
    this.querySelector(".profile-heading")?.insertAdjacentElement("afterend", alert);
    window.setTimeout(() => this.dismissNotification(alert), 3000);
  }

  private dismissNotification(notification: HTMLElement): void {
    notification.classList.add("is-leaving");
    window.setTimeout(() => notification.remove(), 200);
  }
}

const initializeProfile = (): void => {
  const profileRoot = document.querySelector<HTMLElement>("#profile-wrapper");
  if (profileRoot && !profileRoot.hasAttribute("data-controller-initialized")) {
    profileRoot.setAttribute("data-controller-initialized", "true");
    new ProfileController(profileRoot);
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeProfile, { once: true });
} else {
  initializeProfile();
}
