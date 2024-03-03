import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'customDate',
  standalone: true
})
export class CustomDatePipe implements PipeTransform {
  transform(value: number | string): string {
    const date = new Date(value);
    const ordinal = this.ordinal(date.getDate());
    const month = this.month(date.getMonth());
    const year = date.getFullYear();

    let dateString = `${date.getDate()}${ordinal} of ${month}`;

    if (this.currentYear() !== year) {
      dateString += `, ${year}`;
    }

    return dateString;
  }

  private ordinal(day: number): string {
    const date = day % 10;

    switch(date) {
      case 1:
        return 'st';
        break;
      case 2:
        return 'nd';
        break;
      case 3:
        return 'rd';
        break;
      default:
        return 'th';
        break;
    }
  }

  private month(month: number): string {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    return months[month];
  }

  private currentYear(): number {
    return new Date().getFullYear();
  }

}
