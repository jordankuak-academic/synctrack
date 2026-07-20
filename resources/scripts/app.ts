import { ControllerClass } from "./types/Controller";

const controllers = import.meta.glob<{ default: ControllerClass }>("./pages/*.ts");

const initializePageControllers = async (): Promise<void> => {
  const wrappers = document.querySelectorAll<HTMLElement>("[id$='-wrapper']");
  
  for (const wrapper of wrappers) {
    if (wrapper.hasAttribute("data-controller-manual")) {
      continue;
    }

    const page = wrapper.id.replace("-wrapper", "");
    const loader = controllers[`./pages/${page}.ts`];
    
    if (loader == null) {
      continue;
    }
    
    const { default: Controller } = await loader();
    new Controller(wrapper);
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => void initializePageControllers(), { once: true });
} else {
  void initializePageControllers();
}
