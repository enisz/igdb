import { AfterViewInit, Directive, ElementRef, HostListener, OnInit, Renderer2 } from '@angular/core';
import { SearchService } from '../service/search.service';

@Directive({
  selector: '[searchField]',
  standalone: true
})
export class SearchFieldDirective implements AfterViewInit {

  constructor(
    private readonly elementRef: ElementRef,
    private readonly searchService: SearchService,
    private readonly renderer: Renderer2,
    ) { }

  public ngAfterViewInit(): void {
    this.renderer.setStyle(this.elementRef.nativeElement, 'cursor', 'pointer');
    this.renderer.setAttribute(this.elementRef.nativeElement, 'placeholder', 'Search the docs...');
    const nextSibling = this.elementRef.nativeElement.nextSibling;
    const parent = this.elementRef.nativeElement.parentNode;

    if (nextSibling) {
      this.renderer.setProperty(nextSibling, 'disabled', true);
    }

    if (parent) {
      this.renderer.setProperty(parent, 'autocomplete', 'off');
    }
  }

  @HostListener('click')
  onClick(): void {
    this.searchService.setModalVisibility(true);
    this.renderer.setProperty(this.elementRef.nativeElement, 'focus', false);
  }
}
