import { AfterViewInit, Directive, ElementRef } from "@angular/core";

@Directive({
    selector: '[autofocus]',
    standalone: true,
})
export class AutofocusDirective implements AfterViewInit {
    public constructor(
        private readonly elementRef: ElementRef,
    ) {}

    public ngAfterViewInit(): void {
        this.elementRef.nativeElement.focus();
    }
}
