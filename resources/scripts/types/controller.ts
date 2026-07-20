import type Controller from "@/utils/controller";

export type ControllerClass = new (element: HTMLElement) => Controller;