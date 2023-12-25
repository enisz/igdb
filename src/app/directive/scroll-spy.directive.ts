import { Directive, ElementRef, HostListener } from '@angular/core';

@Directive({
  selector: '[scrollspy]',
  standalone: true
})
export class ScrollSpyDirective {
  constructor(
    private readonly elementRef: ElementRef
  ) {
    console.log(elementRef)
  }

  @HostListener('window:scroll', ['$event'])
  public onScroll(event: Event): void {
    console.log('spy scroll');
  }

}
