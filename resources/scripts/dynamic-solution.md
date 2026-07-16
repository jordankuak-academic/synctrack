# Dynamic Import JavaScript Or TypeScript Module

## Method 01

```typescript
document.addEventListener("DOMContentLoaded", async () => {
  const projectWrapper = document.getElementById("XXX-wrapper");
  
  if (projectWrapper instanceof HTMLElement) {
    const { default: XXXController } = await import("./pages/XXX");
    new XXXController(projectWrapper);
  }
});
```

Disadvantage:
- Need to know the module name in advance.
- Need to handle the case where the module is not found.
- More If-Else statements when more module added in future

## Method 02

```typescript
const routes = [
  {
    id: "project-wrapper",
    loader: () => import("./pages/project")
  },
  {
    id: "dashboard-wrapper",
    loader: () => import("./pages/dashboard")
  },
  {
    id: "user-wrapper",
    loader: () => import("./pages/user")
  }
];

document.addEventListener("DOMContentLoaded", async () => {
  for (const route of routes) {
    const element = document.getElementById(route.id);
    
    if (!(element instanceof HTMLElement)) continue;
    
    const module = await route.loader();
    new module.default(element);
    break;
  }
});
```

Disadvantage:
- Need to know the module name in advance.
- Need to add more routes when more module added in future.

## Method 03

```typescript
import { ControllerClass } from "./types/controller";

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
```

Disadvantage:
- Need to make sure the html element has been promised in constant format.
- Need to make sure the respective TypeScript module is in the designed directory.

## Method 04

```typescript
const modules = import.meta.glob("./pages/**/*.ts");

for (const element of document.querySelectorAll("[data-controller]")) {

    const name = element.dataset.controller!;

    const module = modules[`./pages/${name}.ts`];

    if (!module) continue;

    const { default: Controller } = await module();

    new Controller(element as HTMLElement);
}
```

```html
<div data-controller="project" id="project-wrapper"></div>
```
