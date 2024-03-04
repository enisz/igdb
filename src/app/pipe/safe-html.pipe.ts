import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
@Pipe({
  name: 'safeHtml',
  standalone: true
})
export class SafeHtmlPipe implements PipeTransform {

  public constructor(private readonly domSanitizer: DomSanitizer) {}

  transform(value: string, ...args: unknown[]): SafeHtml {
    return this.domSanitizer.bypassSecurityTrustHtml(value);
  }

}
