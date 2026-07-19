import Controller from "@/utils/Controller";
import { AuthTab, AuthStep } from "@/types/Authentication";

export default class LoginController extends Controller {
  private is_form_submitting = false;
  
  protected initialize(): void {
    this.bindEvent();
    this.applyInterceptor();
  }
  
  private bindEvent(): void {
    this.changeTabContent();
    this.changeNextPage();
    this.changePreviousPage();
    this.togglePassword();
  }
  
  private applyInterceptor(): void {
    this.pageRefreshInterceptor();
  }
  
  private changeTabContent(): void {
    const nav_items = Array.from(this.querySelectorAll<HTMLElement>(".nav-container .nav-item[data-tab]"));
    
    this.setActiveTab(this.getCurrentTab());
    
    nav_items.forEach((nav_item) => {
      nav_item.addEventListener("click", () => {
        const selected_tab: AuthTab = nav_item.dataset.tab === "sign-up" ? "sign-up" : "login";
        this.setActiveTab(selected_tab);
        this.persistAuthViewState();
      });
    });
  }
  
  private changeNextPage(): void {
    const next_btn = this.querySelector<HTMLButtonElement>("#next-btn");
    const step_1 = this.querySelector<HTMLElement>("#step-1");
    
    if (!next_btn || !step_1) {
      return;
    }
    
    next_btn.addEventListener("click", () => {
      const step_1_inputs = Array.from(step_1.querySelectorAll<HTMLInputElement>("input"));
      const is_step_1_valid = step_1_inputs.every((input_element) => input_element.reportValidity());
      
      if (!is_step_1_valid) {
        return;
      }
      
      this.setActiveStep("2");
      this.persistAuthViewState();
    });
  }
  
  private changePreviousPage(): void {
    const back_btn = this.querySelector<HTMLButtonElement>("#back-btn");
    
    if (!back_btn) {
      return;
    }
    
    back_btn.addEventListener("click", () => {
      this.setActiveStep("1");
      this.persistAuthViewState();
    });
  }
  
  private togglePassword(): void {
    const password_toggle_buttons = Array.from(this.querySelectorAll<HTMLButtonElement>(".password-toggle-btn"));
    
    if (password_toggle_buttons.length === 0) {
      return;
    }
    
    password_toggle_buttons.forEach((toggle_button) => {
      const form_group = toggle_button.closest(".form-group");
      const password_input = form_group?.querySelector<HTMLInputElement>("input");
      
      if (!password_input) {
        return;
      }
      
      const sync_toggle_state = (): void => {
        const is_visible = password_input.type === "text";
        toggle_button.classList.toggle("is-visible", is_visible);
        toggle_button.setAttribute("aria-pressed", String(is_visible));
        toggle_button.setAttribute("aria-label", is_visible ? "Hide password" : "Show password");
      };
      
      sync_toggle_state();
      
      toggle_button.addEventListener("click", () => {
        password_input.type = password_input.type === "password" ? "text" : "password";
        sync_toggle_state();
      });
    });
  }
  
  private pageRefreshInterceptor(): void {
    const saved_tab = window.sessionStorage.getItem("synctrack.auth.tab");
    const saved_step = window.sessionStorage.getItem("synctrack.auth.step");
    const active_tab: AuthTab = saved_tab === "sign-up" ? "sign-up" : this.getCurrentTab();
    const active_step: AuthStep = this.isReloadNavigation() ? "1" : saved_step === "2" ? "2" : this.getCurrentStep();
    const auth_forms = Array.from(this.querySelectorAll<HTMLFormElement>("form"));
    
    this.setActiveTab(active_tab);
    this.setActiveStep(active_step);
    this.persistAuthViewState();
    
    auth_forms.forEach((auth_form) => {
      auth_form.addEventListener("submit", () => {
        this.is_form_submitting = true;
        const is_sign_up_form = auth_form.closest(".sign-up-container") !== null;
        
        if (is_sign_up_form) {
          window.sessionStorage.setItem("synctrack.auth.tab", "sign-up");
          window.sessionStorage.setItem("synctrack.auth.step", "1");
          return;
        }
        
        this.persistAuthViewState();
      });
    });
    
    window.addEventListener("beforeunload", () => {
      if (this.is_form_submitting) {
        return;
      }
      
      this.persistAuthViewState();
    });
  }
  
  private isReloadNavigation(): boolean {
    const navigation_entry = window.performance.getEntriesByType("navigation")[0] as PerformanceNavigationTiming | undefined;
    return navigation_entry?.type === "reload";
  }
  
  private getCurrentTab(): AuthTab {
    const nav_content = this.querySelector<HTMLElement>(".nav-content");
    return nav_content?.dataset.content === "sign-up" ? "sign-up" : "login";
  }
  
  private setActiveTab(tab: AuthTab): void {
    const nav_content = this.querySelector<HTMLElement>(".nav-content");
    const login_container = this.querySelector<HTMLElement>(".login-container");
    const sign_up_container = this.querySelector<HTMLElement>(".sign-up-container");
    const nav_items = Array.from(this.querySelectorAll<HTMLElement>(".nav-container .nav-item[data-tab]"));
    
    if (!nav_content || !login_container || !sign_up_container) {
      return;
    }
    
    nav_content.dataset.content = tab;
    login_container.classList.toggle("active", tab === "login");
    sign_up_container.classList.toggle("active", tab === "sign-up");
    nav_items.forEach((nav_item) => nav_item.classList.toggle("active", nav_item.dataset.tab === tab));
  }
  
  private getCurrentStep(): AuthStep {
    const sign_up_container = this.querySelector<HTMLElement>(".sign-up-container");
    return sign_up_container?.dataset.step === "2" ? "2" : "1";
  }
  
  private setActiveStep(step: AuthStep): void {
    const step_1 = this.querySelector<HTMLElement>("#step-1");
    const step_2 = this.querySelector<HTMLElement>("#step-2");
    const sign_up_container = this.querySelector<HTMLElement>(".sign-up-container");
    
    if (!step_1 || !step_2 || !sign_up_container) {
      return;
    }
    
    step_1.classList.toggle("active", step === "1");
    step_2.classList.toggle("active", step === "2");
    sign_up_container.dataset.step = step;
  }
  
  private persistAuthViewState(): void {
    window.sessionStorage.setItem("synctrack.auth.tab", this.getCurrentTab());
    window.sessionStorage.setItem("synctrack.auth.step", this.getCurrentStep());
  }
}
