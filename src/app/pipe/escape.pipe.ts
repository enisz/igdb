import { Pipe, PipeTransform } from '@angular/core';
import Underscore from 'underscore';

@Pipe({
  name: 'escape',
  standalone: true
})
export class EscapePipe implements PipeTransform {

  transform(value: string, ...args: unknown[]): string {
    return Underscore.escape(value);
  }

}
