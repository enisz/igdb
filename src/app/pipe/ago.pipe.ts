import { Pipe, PipeTransform } from "@angular/core";
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';

TimeAgo.addDefaultLocale(en)
@Pipe({
    name: 'ago',
    standalone: true,
})
export class AgoPipe implements PipeTransform {
    private timeAgo: TimeAgo;

    public constructor() {
        this.timeAgo = new TimeAgo('en-US')
    }

    public transform(value: number, ...args: any[]): string {
        return this.timeAgo.format(
            new Date(value)
        );
    }
}
