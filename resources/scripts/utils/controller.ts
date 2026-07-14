export default abstract class Controller {
  constructor(protected readonly rootElement: HTMLElement) {
    this.initialize();
  }
  
  protected abstract initialize(): void;
  
  protected querySelector<T extends Element>(selector: string): T | null {
    return this.rootElement.querySelector(selector);
  }
  
  protected querySelectorAll<T extends Element>(selector: string): NodeListOf<T> {
    return this.rootElement.querySelectorAll(selector);
  }
  
  protected hasElement(selector: string): boolean {
    return this.querySelector(selector) !== null;
  }
  
  protected addEventListener<K extends keyof HTMLElementEventMap>(selector: string, event: K, callback: (this: HTMLElement, event: HTMLElementEventMap[K]) => void) {
    const element = this.querySelector<HTMLElement>(selector);
    if (element) {
      element.addEventListener(event, callback);
    }
  }
}