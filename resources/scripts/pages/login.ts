import Controller from "@/utils/controller";

export default class LoginController extends Controller {
  protected initialize(): void {
    this.bindEvents();
  }
  
  private bindEvents(): void {
    this.changeTabNavigationEvent();
    this.passwordToggleEvent();
    this.changeNextFormEvent();
  }
  
  private changeTabNavigationEvent(): void {
    const tabItems = this.querySelectorAll<HTMLDivElement>(".navigation-tab .tab-item");
    const contentItems = [
      this.querySelector<HTMLElement>(".sign-in-wrapper"),
      this.querySelector<HTMLElement>(".sign-up-wrapper"),
    ];
    
    if (tabItems.length !== contentItems.length || contentItems.includes(null)) {
      return;
    }
    
    tabItems.forEach((tabItem, index) => {
      tabItem.addEventListener("click", () => {
        tabItems.forEach((item) => item.classList.remove("active"));
        contentItems.forEach((item) => item?.classList.remove("active"));
        
        tabItem.classList.add("active");
        contentItems[index]?.classList.add("active");
      });
    });
  }
  
  private passwordToggleEvent(): void {
    const toggleButtons = this.querySelectorAll<HTMLButtonElement>("[data-password-toggle]");
    
    toggleButtons.forEach((toggleButton) => {
      toggleButton.addEventListener("click", () => {
        const targetId = toggleButton.dataset.target;
        
        if (targetId == null) {
          return;
        }
        
        const targetInput = this.querySelector<HTMLInputElement>(`#${targetId}`);
        
        if (targetInput == null) {
          return;
        }
        
        const isVisible = targetInput.type === "text";
        const nextType = isVisible ? "password" : "text";
        const nextLabel = isVisible ? "Show password" : "Hide password";
        
        targetInput.type = nextType;
        toggleButton.classList.toggle("is-visible", !isVisible);
        toggleButton.setAttribute("aria-pressed", String(!isVisible));
        toggleButton.setAttribute("aria-label", nextLabel);
      });
    });
  }
  
  private changeNextFormEvent(): void {
    const signUpSteps = Array.from(this.querySelectorAll<HTMLElement>(".sign-up-wrapper .sign-up-step"));
    const nextButton = this.querySelector<HTMLButtonElement>(".sign-up-wrapper .step-btn[type='button']");
    
    if (signUpSteps.length < 2 || nextButton == null) {
      return;
    }
    
    nextButton.addEventListener("click", () => {
      const activeIndex = signUpSteps.findIndex((step) => step.classList.contains("active"));
      const nextIndex = activeIndex + 1;
      
      if (activeIndex < 0 || nextIndex >= signUpSteps.length) {
        return;
      }
      
      this.animateSignUpStepTransition(signUpSteps[activeIndex], signUpSteps[nextIndex]);
    });
  }
  
  private animateSignUpStepTransition(currentStep: HTMLElement, nextStep: HTMLElement): void {
    currentStep.classList.remove("is-leaving-left");
    nextStep.classList.remove("is-entering-right");
    nextStep.classList.add("active", "is-entering-right");
    currentStep.classList.add("is-leaving-left");
    
    nextStep.addEventListener("animationend", () => {
      currentStep.classList.remove("active", "is-leaving-left");
      nextStep.classList.remove("is-entering-right");
    }, { once: true });
  }
}



