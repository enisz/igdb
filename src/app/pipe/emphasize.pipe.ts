import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
    standalone: true,
    name: 'emphasize'
})
export class EmphasizePipe implements PipeTransform {
    transform(value: string, emphasize: string) {
        return emphasize.length ? value.replaceAll(new RegExp(`(${emphasize})`, 'gi'), '<mark>$1</mark>') : value;
    }
}
