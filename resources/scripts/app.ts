import { ControllerClass } from "./types/Controller";

const controllers = import.meta.glob<{ default: ControllerClass }>("./pages/*.ts");

document.addEventListener("DOMContentLoaded", async () => {
  const wrappers = document.querySelectorAll<HTMLElement>("[id$='-wrapper']");
  
  for (const wrapper of wrappers) {
    const page = wrapper.id.replace("-wrapper", "");
    const loader = controllers[`./pages/${page}.ts`];
    
    if (loader == null) {
      continue;
    }
    
    const { default: Controller } = await loader();
    new Controller(wrapper);
  }
});
