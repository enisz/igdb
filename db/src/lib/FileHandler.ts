import Fs from 'fs';

export default class FileHandler {
    public static readFile(path: string): string {
        return Fs.readFileSync(path, { encoding: 'utf-8' });
    }

    public static writeFile(path: string, data: string): void {
        Fs.writeFileSync(path, data, { encoding: 'utf-8'});
    }

    public static readDir(path: string): string[] {
        return Fs.readdirSync(path, { encoding: 'utf-8' });
    }
}
