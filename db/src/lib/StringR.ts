import Slug from 'slug';
import Logger from './Logger';

export default class StringR {
    private static logger = Logger.getLogger(StringR.name);
    public static capitalize(string: string): string {
        return string.charAt(0).toUpperCase() + string.substring(1);
    }

    public static toSlug(string: string): string {
        return Slug(string);
    }

    public static romanize(num: number): string {
        const roman: {[key: string]: number} = {
            M: 1000,
            CM: 900,
            D: 500,
            CD: 400,
            C: 100,
            XC: 90,
            L: 50,
            XL: 40,
            X: 10,
            IX: 9,
            V: 5,
            IV: 4,
            I: 1
        };

        let str = '';

        for (const i of Object.keys(roman)) {
            const q = Math.floor(num / roman[i]);
            num -= q * roman[i];
            str += i.repeat(q);
        }

        return str;
    }
}
