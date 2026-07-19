import type Controller from "@/utils/Controller";

export type ControllerClass = new (element: HTMLElement) => Controller;